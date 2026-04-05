<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Database\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderProcedureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_service_order_procedure(): void
    {
        $procedure = ServiceOrderProcedure::factory()->create();

        $this->assertDatabaseHas('service_order_procedures', [
            'id' => $procedure->id,
            'name' => $procedure->name,
        ]);
    }
}

