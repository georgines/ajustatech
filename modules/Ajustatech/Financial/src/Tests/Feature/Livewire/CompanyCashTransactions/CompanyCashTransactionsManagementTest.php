<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\CompanyCashTransactions;

use Ajustatech\Financial\Livewire\CompanyCashTransactionsManagement;
use Ajustatech\Financial\Database\Models\CompanyCashTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyCashTransactionsManagementTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(CompanyCashTransactionsManagement::class)
			->assertStatus(200);
	}
}
