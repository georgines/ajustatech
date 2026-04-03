<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPendingAnalysisServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_screen_lists_pending_and_in_progress_analysis_services(): void
    {
        $type = AnalysisType::factory()->create([
            'name' => 'Analise de Notebook',
        ]);

        $order = ServiceOrder::factory()->create([
            'customer_name' => 'Cliente Fila',
            'equipment_name' => 'Notebook',
        ]);

        ServiceOrderAnalysisService::factory()->create([
            'service_order_id' => $order->id,
            'analysis_type_id' => $type->id,
            'analysis_type_snapshot' => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ],
            'status' => 'pending',
        ]);

        $this->get(route('service-order-analysis-execution-queue'))
            ->assertOk()
            ->assertSee('Analises a Realizar')
            ->assertSee('Cliente Fila')
            ->assertSee('Comecar analise');
    }
}

