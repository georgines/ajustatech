<?php

namespace Ajustatech\Financial\Tests\Feature;

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
            ":transferHash"=> ""
        ];


        $balance_chaseBank = $chaseBank->getBalance();
        $balance_bankOfAmerica = $bankOfAmerica->getBalance();

        $transferData = $chaseBank->transfer($amount);

        $placeholders[':transferHash'] = $transferData->get("hash");
        $description1 = $this->replacePlaceholders($description1, $placeholders);
        $description2 = $this->replacePlaceholders($description2, $placeholders);

        $receipt_bankOfAmerica = $bankOfAmerica->receive($transferData, $description1);
        $receipt_chaseBank = $chaseBank->confirmTransfer($transferData, $description2);

        dump($description1, $description2);

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
}
