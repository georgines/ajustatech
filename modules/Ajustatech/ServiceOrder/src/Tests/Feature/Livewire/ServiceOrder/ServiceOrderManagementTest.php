<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\ServiceOrder\Livewire\ServiceOrderManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrderManagementTest extends TestCase
{
	use RefreshDatabase;

	public function test_renders_successfully()
	{
		Livewire::test(ServiceOrderManagement::class)
			->assertStatus(200);
	}
}
