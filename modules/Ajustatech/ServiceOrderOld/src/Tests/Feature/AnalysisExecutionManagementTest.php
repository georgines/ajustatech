<?php

namespace Ajustatech\ServiceOrderOld\Tests\Feature;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Services\AnalysisExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Ajustatech\ServiceOrderOld\Livewire\AnalysisExecutionManagement;

class AnalysisExecutionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_execution_respects_sequence_and_requires_answer_to_advance(): void
    {
        $type = AnalysisType::factory()->create([
            'name' => 'Analise Sequencial',
            'slug' => 'analise-sequencial',
        ]);

        $section = $type->sections()->create([
            'name' => 'Secao teste',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $section->questions()->create([
            'code' => 'Q1',
            'prompt' => 'Pergunta obrigatoria 1',
            'answer_type' => 'text',
            'sort_order' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        $section->questions()->create([
            'code' => 'Q2',
            'prompt' => 'Pergunta obrigatoria 2',
            'answer_type' => 'text',
            'sort_order' => 2,
            'is_required' => true,
            'is_active' => true,
        ]);

        $order = ServiceOrder::factory()->create();
        $execution = app(AnalysisExecutionService::class)->createAnalysisServiceInstance($order->id, $type->id);

        Livewire::test(AnalysisExecutionManagement::class, ['id' => $execution->id])
            ->assertSee('Pergunta obrigatoria 1')
            ->call('nextQuestion')
            ->assertHasErrors(['answer'])
            ->set('answers.' . $execution->questions()->orderBy('sort_order')->first()->id, 'Resposta 1')
            ->call('nextQuestion')
            ->assertSet('currentQuestionIndex', 1);
    }

    public function test_can_finalize_after_answering_last_required_question(): void
    {
        $type = AnalysisType::factory()->create([
            'name' => 'Analise Finalizacao',
            'slug' => 'analise-finalizacao',
        ]);

        $section = $type->sections()->create([
            'name' => 'Secao unica',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $question = $section->questions()->create([
            'code' => 'QF',
            'prompt' => 'Pergunta final obrigatoria',
            'answer_type' => 'text',
            'sort_order' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        $order = ServiceOrder::factory()->create();
        $execution = app(AnalysisExecutionService::class)->createAnalysisServiceInstance($order->id, $type->id);
        $instancedQuestion = $execution->questions()->firstOrFail();

        Livewire::test(AnalysisExecutionManagement::class, ['id' => $execution->id])
            ->set('answers.' . $instancedQuestion->id, 'Resposta final')
            ->call('finalizeAnalysis')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_order_analysis_services', [
            'id' => $execution->id,
            'status' => 'finalized',
        ]);
    }
}

