<?php

namespace Ajustatech\Financial\Tests\Feature;


use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyCashTest extends TestCase
{
	use RefreshDatabase;


	public function test_can_create_company_cash()
	{
		$company_cash =  CompanyCash::factory()->create();
		$this->assertDatabaseHas('company_cashes', [
            'id' => $company_cash->id,
		]);
	}
}
