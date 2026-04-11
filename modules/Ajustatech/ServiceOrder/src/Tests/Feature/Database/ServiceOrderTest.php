<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Database;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderTest extends TestCase
{
	use RefreshDatabase;


	public function test_can_create_service_order()
	{
		$service_order =  ServiceOrder::factory()->create();
		$this->assertDatabaseHas('service_order_bases', [
            'id' => $service_order->id,
		]);
	}
}
