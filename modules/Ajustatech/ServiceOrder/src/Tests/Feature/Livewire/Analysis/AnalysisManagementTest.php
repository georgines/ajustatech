<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AnalysisManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_analysis_service_with_questions_and_procedure_rules(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise de notebook')
            ->set('description', 'Checklist tecnico completo')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.sequence', 1)
            ->set('questions.0.question_text', 'O SSD esta funcionando?')
            ->set('questions.0.question_type', 'yes_no')
            ->set('questions.0.answer_procedure_map.no.procedure_id', '')
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $this->assertDatabaseHas('service_order_analysis_services', [
            'name' => 'Analise de notebook',
            'value' => '90.00',
        ]);

        $this->assertDatabaseHas('service_order_analysis_questions', [
            'section_name' => 'Hardware',
            'question_text' => 'O SSD esta funcionando?',
            'question_type' => 'yes_no',
        ]);

        $question = DB::table('service_order_analysis_questions')
            ->where('question_text', 'O SSD esta funcionando?')
            ->first();

        $this->assertNotNull($question);
        $this->assertStringContainsString('yes', (string) $question->answer_procedure_map_json);
        $this->assertNull($question->images_json);
    }

    public function test_collapsed_state_is_persisted_and_restored_on_edit(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Persistencia',
            'description' => 'Teste de colapso',
            'value' => 100.00,
        ], [
            [
                'client_key' => 'q1',
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta 1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'is_collapsed' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'q2',
                'sequence' => 2,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta 2',
                'question_type' => 'yes_no',
                'is_required' => true,
                'is_collapsed' => false,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $secondQuestionId = (string) DB::table('service_order_analysis_questions')
            ->where('analysis_service_id', $service->id)
            ->where('question_text', 'Pergunta 2')
            ->value('id');
        $this->assertNotSame('', $secondQuestionId);

        $this->assertDatabaseHas('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta 1',
            'is_collapsed' => 1,
        ]);
        $this->assertDatabaseHas('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta 2',
            'is_collapsed' => 0,
        ]);

        $component = Livewire::test(AnalysisManagement::class, ['id' => $service->id]);
        $questionsBeforeToggle = collect($component->get('questions'))->values();
        $this->assertCount(2, $questionsBeforeToggle);

        $firstBefore = (bool) data_get($questionsBeforeToggle[0] ?? [], 'is_collapsed', false);
        $secondBefore = (bool) data_get($questionsBeforeToggle[1] ?? [], 'is_collapsed', false);

        $component
            ->call('toggleQuestionCollapseByIndex', 1)
            ->assertSet('questions.1.is_collapsed', !$secondBefore);

        $component
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $reloaded = ServiceOrderAnalysisService::findWithQuestionsOrFail($service->id);
        $questions = $reloaded->questions->values();

        $this->assertSame($firstBefore, (bool) ($questions[0]->is_collapsed ?? false));
        $this->assertSame(!$secondBefore, (bool) ($questions[1]->is_collapsed ?? false));
    }
}
