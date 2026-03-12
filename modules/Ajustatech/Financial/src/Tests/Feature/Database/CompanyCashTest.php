<?php

namespace Ajustatech\Financial\Tests\Feature\Database;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompanyCashTest extends TestCase
{
    use RefreshDatabase;

    protected function replacePlaceholders(string $descriptionTemplate, array $placeholders): string
    {
        return strtr($descriptionTemplate, $placeholders);
    }

    public function test_can_transfer_amount_with_custom_messages()
    {
        $chaseBank = CompanyCash::createNew([
            "user_id" => "123456-654321-987654-321987",
            "user_name" => "John Doe",
            "cash_name" => "Chase Bank",
            "description" => "Account for receiving payments",
            "agency" => "100",
            "account" => "12345 - 6",
            "balance_amount" => 2500.5,
            "balance_description" => "initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        $bankOfAmerica = CompanyCash::createNew([
            "user_id" => "123456-654321-987654-321987",
            "user_name" => "John Doe",
            "cash_name" => "Bank of America",
            "description" => "Account for transfers",
            "agency" => "2845 - 2",
            "account" => "98765 - 4",
            "balance_amount" => 1000,
            "balance_description" => "initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        $description1 = "Transfer of $:amount received from :originCashName - :originCashId. Transaction code: :transferHash.";
        $description2 = "Transfer of $:amount sent to :destinationCashName - :destinationCashId. Transaction code: :transferHash.";

        $amount = 10;


        $placeholders = [
            ":amount" => $amount,
            ":originCashName" => $chaseBank->cash_name,
            ":originCashId" => $chaseBank->id,
            ":destinationCashName" => $bankOfAmerica->cash_name,
            ":destinationCashId" => $bankOfAmerica->id,
            ":transferHash" => ""
        ];


        $balance_chaseBank = $chaseBank->getBalance();
        $balance_bankOfAmerica = $bankOfAmerica->getBalance();

        $transferData = $chaseBank->transfer($amount);

        $placeholders[':transferHash'] = $transferData->get("hash");
        $description1 = $this->replacePlaceholders($description1, $placeholders);
        $description2 = $this->replacePlaceholders($description2, $placeholders);

        $receipt_bankOfAmerica = $bankOfAmerica->receive($transferData, $description1);
        $receipt_chaseBank = $chaseBank->confirmTransfer($transferData, $description2);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $bankOfAmerica->id,
            'amount' => '10.00',
            'description' => $description1,
        ]);

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $chaseBank->id,
            'amount' => '10.00',
            'description' => $description2,
        ]);
    }

    public function test_can_create_new_account()
    {
        $accountData = [
            "user_id" => Str::uuid()->toString(),
            "user_name" => "Test User",
            "cash_name" => "Test Bank",
            "description" => "Test account description",
            "balance_amount" => 150,
            "is_online" => true,
            "is_active" => true
        ];

        $newAccount = CompanyCash::createNew($accountData);

        $this->assertDatabaseHas('company_cashes', [
            'id' => $newAccount->id,
            'cash_name' => 'Test Bank',
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $newAccount->id,
            'balance' => 150
        ]);
    }

    public function test_can_update_balance_after_inflow_and_outflow()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "cash_name" => "Sample Bank",
            "balance_amount" => 500
        ]);


        $account->registerInflow(200);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 700
        ]);


        $account->registerOutflow(100);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 600
        ]);
    }

    public function test_has_sufficient_balance()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "cash_name" => "Test Bank",
            "balance_amount" => 500
        ]);

        $this->assertTrue($account->hasSufficientBalance(400));
        $this->assertFalse($account->hasSufficientBalance(600));
    }

    public function test_can_register_inflow()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "cash_name" => "Test Bank",
            "balance_amount" => 500
        ]);

        $account->registerInflow(100, "Test inflow");

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $account->id,
            'amount' => 100,
            'description' => 'Test inflow',
            'is_inflow' => true
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 600
        ]);
    }

    public function test_can_register_outflow()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "cash_name" => "Test Bank",
            "balance_amount" => 500
        ]);

        $account->registerOutflow(200, "Test outflow");

        $this->assertDatabaseHas('company_cash_transactions', [
            'company_cash_id' => $account->id,
            'amount' => 200,
            'description' => 'Test outflow',
            'is_inflow' => false
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 300
        ]);
    }

    public function test_can_apply_retroactive_inflow_transaction()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "Test User",
            "cash_name" => "Test Bank",
            "balance_amount" => 500,
            "is_online" => true,
            "is_active" => true
        ]);

        $transaction = $account->registerInflow(100, "Initial inflow");

        $account->applyRetroactiveTransaction($transaction->id, 150);

        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction->id,
            'amount' => 150,
            'is_inflow' => true
        ]);

        // dump(['esperado'=>$account->calculateBalance(), 'calculado'=>$account->getBalance()->balance]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 650
        ]);
    }

    public function test_can_apply_retroactive_outflow_transaction()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "Test User",
            "cash_name" => "Test Bank",
            "balance_amount" => 150,
            "is_online" => true,
            "is_active" => true
        ]);

        $transaction = $account->registerOutflow(100, "Initial outflow");

        $account->applyRetroactiveTransaction($transaction->id, 80);

        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction->id,
            'amount' => 80,
            'is_inflow' => false
        ]);

        // dump(['esperado'=>$account->calculateBalance(), 'calculado'=>$account->getBalance()->balance]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 70
        ]);
    }

    public function test_can_apply_retroactive_outflow_end_inflow_transaction()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "Test User",
            "cash_name" => "Test Bank",
            "balance_amount" => 500,
            "is_online" => true,
            "is_active" => true
        ]);

        $transaction = $account->registerOutflow(180, "Initial outflow");
        $transaction2 = $account->registerInflow(300, "Initial intflow");
        $transaction3 = $account->registerOutflow(150, "Initial outflow");
        $transaction4 = $account->registerInflow(50, "Initial intflow");

        $account->applyRetroactiveTransaction($transaction->id, 170);
        $account->applyRetroactiveTransaction($transaction2->id, 299);
        $account->applyRetroactiveTransaction($transaction3->id, 160);
        $account->applyRetroactiveTransaction($transaction4->id, 60);

        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction->id,
            'amount' => 170,
            'is_inflow' => false
        ]);
        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction2->id,
            'amount' => 299,
            'is_inflow' => true
        ]);
        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction3->id,
            'amount' => 160,
            'is_inflow' => false
        ]);
        $this->assertDatabaseHas('company_cash_transactions', [
            'id' => $transaction4->id,
            'amount' => 60,
            'is_inflow' => true
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 529
        ]);
    }

    public function test_can_calculate_balance()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "Test User",
            "cash_name" => "Test Bank",
            "balance_amount" => 1000,
            "is_online" => true,
            "is_active" => true
        ]);

        $account->registerInflow(200, "Test inflow");
        $account->registerOutflow(100.5, "Test outflow");
        $account->registerOutflow(50, "Test outflow");
        $account->registerInflow(3.1, "Test outflow");

        $balance = $account->calculateBalance();

        $this->assertEquals(1052.6, $balance);
    }

    public function test_can_get_all_cashes_with_balances()
    {
        $cash1 = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "User 1",
            "cash_name" => "Cash 1",
            "balance_amount" => 1000,
            "balance_description" => "Initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        $cash2 = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "user_name" => "User 2",
            "cash_name" => "Cash 2",
            "balance_amount" => 2000,
            "balance_description" => "Initial balance",
            "is_online" => true,
            "is_active" => true
        ]);

        $cashesWithBalances = CompanyCash::getAllCashesWithBalances();

        $this->assertCount(2, $cashesWithBalances);
        $this->assertEquals(1000, $cashesWithBalances[0]->balance);
        $this->assertEquals(2000, $cashesWithBalances[1]->balance);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $cash1->id,
            'balance' => 1000
        ]);

        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $cash2->id,
            'balance' => 2000
        ]);
    }

    public function test_with_difference_applies_correct_balance_update()
    {
        $account = CompanyCash::createNew([
            "user_id" => Str::uuid()->toString(),
            "cash_name" => "Sample Bank",
            "balance_amount" => 100
        ]);

        $account->updateBalance()->withDifference(50, true);
        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 150
        ]);

        $account->updateBalance()->withDifference(30, false);
        $this->assertDatabaseHas('company_cash_balances', [
            'company_cash_id' => $account->id,
            'balance' => 120
        ]);
    }

    public function test_available_company_cash_types()
    {
        $account = new CompanyCash();
        $types = $account->availableCompanyCashsTypes();

        $this->assertEquals(['physical', 'online'], $types);
    }
}
