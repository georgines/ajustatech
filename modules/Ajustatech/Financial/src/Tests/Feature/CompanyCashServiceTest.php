<?php

namespace Modules\Ajustatech\Financial\Tests\Feature;

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

    public function test_creates_new_cash_with_initial_balance()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);

        $this->assertDatabaseHas('company_cashes', [
            'cash_name' => $name,
            'agency' => '1234',
            'account' => '567890',
            'description' => $description,
            'is_online' => true
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'balance' => $initialBalance
        ]);
    }

    public function test_creates_new_physical_cash_with_initial_balance()
    {
        $name = 'Physical Cash';
        $initialBalance = 1000.00;
        $description = 'Main physical cash for operations';

        $this->cashService->createNewPhysicalCash($name, $initialBalance, $description);

        $this->assertDatabaseHas('company_cashes', [
            'cash_name' => $name,
            'description' => $description,
            'is_online' => false
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'balance' => $initialBalance
        ]);
    }

    public function test_deposits_to_cash()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';

        $depositAmount = 250.5;

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->deposit($depositAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $depositAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), 1250.5);
    }

    public function test_throws_exception_on_insufficient_balance_for_withdrawal()
    {
        $this->expectException(InsufficientBalanceException::class);

        $name = 'Main Cash';
        $initialBalance = 499.9;
        $description = 'Main cash for operations';

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);

        $withdrawAmount = 500.00;
        $this->cashService->withdrawal($withdrawAmount);
    }

    public function test_withdraws_from_cash_if_balance_is_sufficient()
    {
        $name = 'Main Cash';
        $initialBalance = 0;
        $description = 'Main cash for operations';

        $depositAmount = 1000.00;
        $withdrawAmount = 500.00;

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->deposit($depositAmount);
        $this->cashService->withdrawal($withdrawAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $withdrawAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), $depositAmount - $withdrawAmount);
    }

    public function test_transfers_between_two_cashes()
    {
        $depositAmount = 1000.00;

        $companyCash1 = CompanyCash::createNew(['cash_name' => 'bank', 'balance_amount' => 0]);
        $companyCash2 = CompanyCash::createNew(['cash_name' => 'bank2', 'balance_amount' => 0]);

        $cashService1 = new CompanyCashService($companyCash1);
        $cashService1->deposit($depositAmount);

        $cashService2 = new CompanyCashService($companyCash2);

        CompanyCashService::transferBetweenCompanyCashes(255.1, $companyCash1->id, $companyCash2->id);

        $this->assertEquals($companyCash1->calculateBalance(), 744.9);
        $this->assertEquals($cashService2->calculateBalance(), 255.1);
    }


    public function test_receives_payment()
    {
        $name = 'Main Cash';
        $initialBalance = 0;
        $description = 'Main cash for operations';
        $receiveAmount = 500.00;
        $customerName = 'John Doe';

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->toReceive($receiveAmount, $customerName);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $receiveAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), $receiveAmount);
    }

    public function test_pay_to_recipient()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';
        $payAmount = 250.00;
        $recipientName = 'Jane Doe';

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->pay($payAmount, $recipientName);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $payAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), $initialBalance - $payAmount);
    }

    public function test_bank_fees()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';
        $feeAmount = 50.00;

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->bankFees($feeAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $feeAmount,
            'is_inflow' => false
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), $initialBalance - $feeAmount);
    }

    public function test_refund_transaction()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';
        $refundAmount = 200.00;
        $transactionType = 'purchase';

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $this->cashService->refund($refundAmount, $transactionType);

        $this->assertDatabaseHas('company_cash_transactions', [
            'amount' => $refundAmount,
            'is_inflow' => true
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), $initialBalance + $refundAmount);
    }

    public function test_apply_retroactive_transaction()
    {
        $name = 'Main Cash';
        $initialBalance = 1000.00;
        $description = 'Main cash for operations';
        $newAmount = 200;

        $this->cashService->createNewCash($name, $initialBalance, '1234', '567890', $description);
        $transaction = $this->cashService->toReceive(100, 'jhon doe');
        $this->cashService->applyRetroactiveTransaction($transaction->id, $newAmount);

        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction->id,
            'amount' => $newAmount
        ]);

        $this->assertEquals($this->cashService->calculateBalance(), 1200);
    }
}
