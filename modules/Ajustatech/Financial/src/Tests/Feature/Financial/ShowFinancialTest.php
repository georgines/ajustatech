<?php

namespace Ajustatech\Financial\Tests\Feature\Financial;

use Ajustatech\Financial\Livewire\ShowFinancial;
use Ajustatech\Financial\Database\Models\Financial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowFinancialTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(ShowFinancial::class)
			->assertStatus(200);
	}
}
