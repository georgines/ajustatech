<?php

namespace Ajustatech\Financial\Tests\Feature\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Exceptions\InsufficientBalanceException;
use Ajustatech\Financial\Services\CompanyCashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyCashServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CompanyCashService $cashService;
    protected CompanyCash $companyCash;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyCash = new CompanyCash;
        $this->cashService = new CompanyCashService($this->companyCash);
    }

    private function createSampleCash(float $initialBalance, string $name = 'Sample Cash', bool $isOnline = true): CompanyCash
    {
        return $this->cashService->createCash($name, $initialBalance, '1234', '567890', 'Sample cash for testing', $isOnline);
    }

    public function test_create_cash_with_initial_balance_should_persist_data()
    {
        $initialBalance = 1000.00;
        $cash = $this->createSampleCash($initialBalance);

        $this->assertDatabaseHas('company_cashes', [
            'cash_name' => 'Sample Cash',
            'agency' => '1234',
            'account' => '567890',
            'description' => 'Sample cash for testing',
            'is_online' => true
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $cash->id,
            'balance' => $initialBalance
        ]);
    }

    public function test_create_physical_cash_with_initial_balance_should_persist_data()
    {
        $initialBalance = 1000.00;
        $cash = $this->createSampleCash($initialBalance, 'Physical Cash', false);

        $this->assertDatabaseHas('company_cashes', [
            'cash_name' => 'Physical Cash',
            'description' => 'Sample cash for testing',
            'is_online' => false
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $cash->id,
            'balance' => $initialBalance
        ]);
    }

    public function test_deposit_should_increase_cash_balance()
    {
        $initialBalance = 1000.00;
        $depositAmount = 250.5;

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->deposit($depositAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $depositAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals(1250.5, $this->cashService->calculateBalance());
    }

    public function test_withdraw_should_throw_exception_when_balance_is_insufficient()
    {
        $this->expectException(InsufficientBalanceException::class);

        $initialBalance = 499.9;
        $cash = $this->createSampleCash($initialBalance);

        $this->cashService->withdrawal(500.00);
    }

    public function test_withdraw_should_decrease_balance_when_balance_is_sufficient()
    {
        $initialBalance = 0;
        $depositAmount = 1000.00;
        $withdrawAmount = 500.00;

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->deposit($depositAmount);
        $this->cashService->withdrawal($withdrawAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $withdrawAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals(500.00, $this->cashService->calculateBalance());
    }

    public function test_transfer_between_cashes_should_update_balances()
    {
        $depositAmount = 1000.00;

        $companyCash1 = CompanyCash::createNew(['cash_name' => 'Cash 1', 'balance_amount' => 0]);
        $companyCash2 = CompanyCash::createNew(['cash_name' => 'Cash 2', 'balance_amount' => 0]);

        $cashService1 = new CompanyCashService($companyCash1);
        $cashService1->deposit($depositAmount);

        CompanyCashService::transferBetweenCompanyCashes(255.1, $companyCash1->id, $companyCash2->id);

        $this->assertEquals(744.9, $companyCash1->calculateBalance());
        $this->assertEquals(255.1, $companyCash2->calculateBalance());
    }

    public function test_receive_payment_should_increase_balance()
    {
        $initialBalance = 0;
        $receiveAmount = 500.00;
        $customerName = 'John Doe';

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->toReceive($receiveAmount, $customerName);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $receiveAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals($receiveAmount, $this->cashService->calculateBalance());
    }

    public function test_pay_to_recipient_should_decrease_balance()
    {
        $initialBalance = 1000.00;
        $payAmount = 250.00;
        $recipientName = 'Jane Doe';

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->pay($payAmount, $recipientName);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $payAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals(750.00, $this->cashService->calculateBalance());
    }

    public function test_bank_fees_should_decrease_balance()
    {
        $initialBalance = 1000.00;
        $feeAmount = 50.00;

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->bankFees($feeAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $feeAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals(950.00, $this->cashService->calculateBalance());
    }

    public function test_refund_should_increase_balance()
    {
        $initialBalance = 1000.00;
        $refundAmount = 200.00;
        $transactionType = 'purchase';

        $cash = $this->createSampleCash($initialBalance);
        $this->cashService->refund($refundAmount, $transactionType);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $cash->id,
            'amount' => $refundAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals(1200.00, $this->cashService->calculateBalance());
    }

    public function test_apply_retroactive_transaction_should_update_transaction_amount()
    {
        $initialBalance = 1000.00;
        $newAmount = 200;

        $cash = $this->createSampleCash($initialBalance);
        $transaction = $this->cashService->toReceive(100, 'John Doe');
        $this->cashService->applyRetroactiveTransaction($transaction->id, $newAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction->id,
            'amount' => $newAmount
        ]);

        $this->assertEquals(1200, $this->cashService->calculateBalance());
    }

    public function test_get_all_company_cashes_with_balances_should_return_correct_data()
    {
        CompanyCash::createNew([
            "cash_name" => "Cash 1",
            "balance_amount" => 1000,
            "balance_description" => "Initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        CompanyCash::createNew([
            "cash_name" => "Cash 2",
            "balance_amount" => 2000,
            "balance_description" => "Initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        $cashesWithBalances = CompanyCashService::getAllCompanyCashs();

        $this->assertCount(2, $cashesWithBalances);
        $this->assertEquals(1000, $cashesWithBalances[0]->balance);
        $this->assertEquals(2000, $cashesWithBalances[1]->balance);
    }
}
