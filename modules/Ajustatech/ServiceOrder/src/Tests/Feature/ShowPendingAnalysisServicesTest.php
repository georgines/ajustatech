<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisSection;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

        $analysisService = ServiceOrderAnalysisService::factory()->create([
            'service_order_id' => $order->id,
            'analysis_type_id' => $type->id,
            'analysis_type_snapshot' => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ],
            'status' => 'pending',
        ]);

        $section = ServiceOrderAnalysisSection::query()->create([
            'id' => (string) Str::uuid(),
            'service_order_analysis_service_id' => $analysisService->id,
            'source_section_id' => null,
            'name' => 'Secao',
            'sort_order' => 1,
        ]);

        $q1 = $analysisService->questions()->create([
            'service_order_analysis_section_id' => $section->id,
            'source_question_id' => null,
            'question_code' => 'Q1',
            'prompt' => 'Pergunta 1',
            'answer_type' => 'text',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => false,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $analysisService->questions()->create([
            'service_order_analysis_section_id' => $section->id,
            'source_question_id' => null,
            'question_code' => 'Q2',
            'prompt' => 'Pergunta 2',
            'answer_type' => 'text',
            'sort_order' => 2,
            'is_required' => true,
            'is_repeatable' => false,
            'requires_photo_evidence' => false,
            'is_active' => true,
        ]);

        $analysisService->responses()->create([
            'service_order_analysis_question_id' => $q1->id,
            'answer_text' => 'respondida',
        ]);

        $this->get(route('service-order-analysis-execution-queue'))
            ->assertOk()
            ->assertSee('Analises a Realizar')
            ->assertSee('Cliente Fila')
            ->assertSee('OS #' . str_pad((string) $order->order_number, 6, '0', STR_PAD_LEFT))
            ->assertSee('50%')
            ->assertSee('Comecar analise');
    }
}
