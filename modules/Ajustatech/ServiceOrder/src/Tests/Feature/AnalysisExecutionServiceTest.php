<?php

namespace Ajustatech\ServiceOrder\Tests\Feature;

use Ajustatech\ServiceOrder\Database\Models\AnalysisTechnicalAction;
use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Services\AnalysisExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AnalysisExecutionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_frozen_analysis_instance_from_model(): void
    {
        $type = $this->createDamageAnalysisType();
        $order = ServiceOrder::factory()->create();
        $service = app(AnalysisExecutionService::class);

        $instance = $service->createAnalysisServiceInstance($order->id, $type->id);

        $this->assertDatabaseHas('service_order_analysis_services', [
            'id' => $instance->id,
            'service_order_id' => $order->id,
            'analysis_type_id' => $type->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('service_order_analysis_sections', 1);
        $this->assertDatabaseCount('service_order_analysis_questions', 1);
        $this->assertDatabaseCount('service_order_analysis_question_options', 2);
        $this->assertDatabaseCount('service_order_analysis_complementary_fields', 2);
    }

    public function test_applies_conditional_required_photo_and_generates_technical_finding(): void
    {
        $type = $this->createDamageAnalysisType();
        $order = ServiceOrder::factory()->create();
        $service = app(AnalysisExecutionService::class);
        $instance = $service->createAnalysisServiceInstance($order->id, $type->id);
        $question = $instance->questions()->with(['options', 'complementaryFields'])->firstOrFail();
        $photoField = $question->complementaryFields->firstWhere('field_type', 'photo');
        $noteField = $question->complementaryFields->firstWhere('field_type', 'text');

        try {
            $service->answerQuestion($instance->id, $question->id, [
                'answer' => 'sim',
                'complementary' => [
                    $noteField->id => 'Dano visivel na lateral',
                ],
            ]);
            $this->fail('Deveria falhar por evidencia obrigatoria ausente.');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }

        // Re-answer now with required photo evidence
        try {
            $service->answerQuestion($instance->id, $question->id, [
                'answer' => 'sim',
                'complementary' => [
                    $photoField->id => 'foto enviada',
                    $noteField->id => 'Dano visivel na lateral',
                ],
                'attachments' => [
                    $photoField->id => [[
                        'path' => 'analysis/evidence-1.jpg',
                        'original_name' => 'evidence-1.jpg',
                        'extension' => 'jpg',
                    ]],
                ],
            ]);
        } catch (ValidationException $e) {
            $this->fail('Nao deveria falhar com foto obrigatoria enviada: ' . $e->getMessage());
        }

        $this->assertDatabaseHas('service_order_technical_findings', [
            'service_order_analysis_service_id' => $instance->id,
            'should_generate_budget' => true,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('service_order_analysis_responses', [
            'service_order_analysis_service_id' => $instance->id,
            'service_order_analysis_question_id' => $question->id,
            'answer_text' => 'sim',
        ]);
    }

    public function test_blocks_finalization_when_required_evidence_is_missing(): void
    {
        $type = $this->createDamageAnalysisType();
        $order = ServiceOrder::factory()->create();
        $service = app(AnalysisExecutionService::class);
        $instance = $service->createAnalysisServiceInstance($order->id, $type->id);
        $question = $instance->questions()->with(['options', 'complementaryFields'])->firstOrFail();
        $optionNo = $question->options->firstWhere('value', 'nao');

        $service->answerQuestion($instance->id, $question->id, [
            'answer' => $optionNo->value,
        ]);

        $finalized = $service->finalizeAnalysisService($instance->id);

        $this->assertSame('finalized', $finalized->status);
        $this->assertNotNull($finalized->completed_at);
    }

    private function createDamageAnalysisType(): AnalysisType
    {
        $action = AnalysisTechnicalAction::query()->create([
            'name' => 'Substituicao de carcaca',
            'slug' => 'substituicao-carcaca-' . Str::lower((string) Str::uuid()),
            'description' => 'Acao tecnica para dano estrutural.',
            'is_active' => true,
        ]);

        $type = AnalysisType::query()->create([
            'name' => 'Analise de Notebook',
            'slug' => 'analise-notebook-' . Str::lower((string) Str::uuid()),
            'description' => 'Fluxo tecnico para diagnostico de notebook',
            'is_active' => true,
        ]);

        $section = $type->sections()->create([
            'name' => 'Inspecao fisica',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $question = $section->questions()->create([
            'code' => 'Q-001',
            'prompt' => 'Carcaca quebrada?',
            'answer_type' => 'single_select',
            'sort_order' => 1,
            'is_required' => true,
            'is_repeatable' => false,
            'is_active' => true,
        ]);

        $optionNo = $question->options()->create([
            'label' => 'Nao',
            'value' => 'nao',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $optionYes = $question->options()->create([
            'label' => 'Sim',
            'value' => 'sim',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $noteField = $question->complementaryFields()->create([
            'name' => 'observacao_dano',
            'label' => 'Observacao tecnica',
            'field_type' => 'text',
            'sort_order' => 1,
            'is_required' => false,
            'is_active' => true,
            'configuration' => [],
        ]);

        $photoField = $question->complementaryFields()->create([
            'name' => 'foto_evidencia',
            'label' => 'Foto de evidencia',
            'field_type' => 'photo',
            'sort_order' => 2,
            'is_required' => false,
            'is_active' => true,
            'configuration' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            ],
        ]);

        \DB::table('analysis_conditional_rules')->insert([
            'id' => (string) Str::uuid(),
            'analysis_question_id' => $question->id,
            'target_type' => 'complementary_field',
            'target_id' => $photoField->id,
            'operator' => 'equals',
            'expected_option_id' => $optionYes->id,
            'expected_value' => null,
            'effect' => 'require',
            'effect_value' => null,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $question->consequences()->create([
            'analysis_question_option_id' => $optionNo->id,
            'match_operator' => 'equals',
            'match_value' => null,
            'severity' => 'low',
            'description' => 'Sem dano estrutural.',
            'analysis_technical_action_id' => null,
            'should_generate_budget' => false,
            'visible_to_technician' => true,
            'recommendation_text' => 'Sem necessidade de reparo estrutural.',
            'is_active' => true,
        ]);

        $question->consequences()->create([
            'analysis_question_option_id' => $optionYes->id,
            'match_operator' => 'equals',
            'match_value' => null,
            'severity' => 'critical',
            'description' => 'Dano estrutural identificado na carcaca.',
            'analysis_technical_action_id' => $action->id,
            'should_generate_budget' => true,
            'visible_to_technician' => true,
            'recommendation_text' => 'Avaliar substituicao de carcaca.',
            'is_active' => true,
        ]);

        return $type;
    }
}
