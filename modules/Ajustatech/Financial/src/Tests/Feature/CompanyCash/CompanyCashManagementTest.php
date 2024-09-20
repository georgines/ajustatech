<?php

namespace Ajustatech\Financial\Tests\Feature\CompanyCash;

use Ajustatech\Financial\Livewire\CompanyCashManagement;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyCashManagementTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(CompanyCashManagement::class)
			->assertStatus(200);
	}
}
