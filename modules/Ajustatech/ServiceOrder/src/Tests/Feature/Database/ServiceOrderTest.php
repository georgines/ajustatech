<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Database;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
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
            'status_flow_id' => $service_order->status_flow_id,
            'order_number' => $service_order->order_number,
		]);
	}

    public function test_assigns_default_status_and_sequence_number_automatically(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $first = ServiceOrder::factory()->create();
        $second = ServiceOrder::factory()->create();

        $defaultStatusId = ServiceOrderStatusFlow::defaultInitialId();

        $this->assertNotNull($first->status_flow_id);
        $this->assertNotNull($first->order_number);
        $this->assertSame($defaultStatusId, $first->status_flow_id);
        $this->assertGreaterThan($first->order_number, $second->order_number);
    }
}
