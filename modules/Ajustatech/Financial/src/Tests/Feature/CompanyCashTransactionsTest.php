<?php

namespace Ajustatech\Financial\Tests\Feature;


use Ajustatech\Financial\Database\Models\CompanyCashTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyCashTransactionsTest extends TestCase
{
	use RefreshDatabase;


	// public function test_can_create_company_cash_transactions()
	// {
	// 	$company_cash_transactions =  CompanyCashTransactions::factory()->create();
	// 	$this->assertDatabaseHas('company_cash_transactions', [
    //         'id' => $company_cash_transactions->id,
	// 	]);
	// }
}
