<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\CompanyCash;

use Ajustatech\Financial\Livewire\ShowCompanyCash;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowCompanyCashTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(ShowCompanyCash::class)
			->assertStatus(200);
	}
}
