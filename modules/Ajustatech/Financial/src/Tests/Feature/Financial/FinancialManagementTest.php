<?php

namespace Ajustatech\Financial\Tests\Feature\Financial;

use Ajustatech\Financial\Livewire\FinancialManagement;
use Ajustatech\Financial\Database\Models\Financial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialManagementTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(FinancialManagement::class)
			->assertStatus(200);
	}
}
