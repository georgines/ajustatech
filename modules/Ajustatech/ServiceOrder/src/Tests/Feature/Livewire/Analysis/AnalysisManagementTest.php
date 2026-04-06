<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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

    public function test_mode_property_cannot_be_changed_from_client(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(AnalysisManagement::class)
            ->set('mode', 'edit');
    }

    public function test_analysis_service_id_property_cannot_be_changed_from_client(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(AnalysisManagement::class)
            ->set('analysisServiceId', (string) Str::uuid());
    }

    public function test_save_rejects_unknown_procedure_ids(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise com procedimento invalido')
            ->set('description', 'Teste de integridade')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta com regra invalida')
            ->set('questions.0.question_type', 'yes_no')
            ->set('questions.0.answer_procedure_map.yes.procedure_id', (string) Str::uuid())
            ->call('save')
            ->assertHasErrors(['questions.0.answer_procedure_map']);

        $this->assertDatabaseMissing('service_order_analysis_services', [
            'name' => 'Analise com procedimento invalido',
        ]);
    }

    public function test_save_requires_at_least_one_option_for_select_questions(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise sem opcoes')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Sistema')
            ->set('questions.0.question_text', 'Qual componente falhou?')
            ->set('questions.0.question_type', 'select')
            ->set('questions.0.options', [])
            ->call('save')
            ->assertHasErrors(['questions.0.options']);

        $this->assertDatabaseMissing('service_order_analysis_services', [
            'name' => 'Analise sem opcoes',
        ]);
    }

    public function test_save_rejects_select_questions_with_more_than_eight_options(): void
    {
        $options = collect(range(1, 9))
            ->map(fn (int $index) => [
                'key' => (string) Str::uuid(),
                'label' => 'Opcao ' . $index,
                'procedure_id' => '',
            ])
            ->all();

        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise com muitas opcoes')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Sistema')
            ->set('questions.0.question_text', 'Qual componente falhou?')
            ->set('questions.0.question_type', 'select')
            ->set('questions.0.options', $options)
            ->call('save')
            ->assertHasErrors(['questions.0.options']);

        $this->assertDatabaseMissing('service_order_analysis_services', [
            'name' => 'Analise com muitas opcoes',
        ]);
    }

    public function test_save_rejects_duplicate_trigger_values_for_sibling_subquestions(): void
    {
        $component = Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise com subpergunta duplicada')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'O SSD esta funcionando?')
            ->set('questions.0.question_type', 'yes_no');

        $parentClientKey = (string) data_get($component->get('questions'), '0.client_key');

        $component
            ->set('questions', [
                [
                    'client_key' => $parentClientKey,
                    'sequence' => 1,
                    'section_name' => 'Hardware',
                    'question_text' => 'O SSD esta funcionando?',
                    'question_type' => 'yes_no',
                    'is_subquestion' => false,
                    'parent_client_key' => '',
                    'condition_value' => '',
                    'is_required' => false,
                    'is_technical_description_required' => false,
                    'is_image_required' => false,
                    'required_images_count' => 1,
                    'has_help' => false,
                    'help_content' => '',
                    'is_collapsed' => false,
                    'options' => [],
                    'answer_procedure_map' => [
                        'yes' => ['procedure_id' => ''],
                        'no' => ['procedure_id' => ''],
                    ],
                ],
                [
                    'client_key' => (string) Str::uuid(),
                    'sequence' => 2,
                    'section_name' => 'Hardware',
                    'question_text' => 'Subpergunta 1',
                    'question_type' => 'yes_no',
                    'is_subquestion' => true,
                    'parent_client_key' => $parentClientKey,
                    'condition_value' => 'no',
                    'is_required' => false,
                    'is_technical_description_required' => false,
                    'is_image_required' => false,
                    'required_images_count' => 1,
                    'has_help' => false,
                    'help_content' => '',
                    'is_collapsed' => false,
                    'options' => [],
                    'answer_procedure_map' => [
                        'yes' => ['procedure_id' => ''],
                        'no' => ['procedure_id' => ''],
                    ],
                ],
                [
                    'client_key' => (string) Str::uuid(),
                    'sequence' => 3,
                    'section_name' => 'Hardware',
                    'question_text' => 'Subpergunta 2',
                    'question_type' => 'yes_no',
                    'is_subquestion' => true,
                    'parent_client_key' => $parentClientKey,
                    'condition_value' => 'no',
                    'is_required' => false,
                    'is_technical_description_required' => false,
                    'is_image_required' => false,
                    'required_images_count' => 1,
                    'has_help' => false,
                    'help_content' => '',
                    'is_collapsed' => false,
                    'options' => [],
                    'answer_procedure_map' => [
                        'yes' => ['procedure_id' => ''],
                        'no' => ['procedure_id' => ''],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasErrors(['questions.2.condition_value']);

        $this->assertDatabaseMissing('service_order_analysis_services', [
            'name' => 'Analise com subpergunta duplicada',
        ]);
    }

    public function test_move_question_down_keeps_subquestions_attached_to_main_block(): void
    {
        $component = Livewire::test(AnalysisManagement::class);

        $firstClientKey = (string) data_get($component->get('questions'), '0.client_key');

        $component->set('questions', [
            [
                'client_key' => $firstClientKey,
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta principal 1',
                'question_type' => 'yes_no',
                'is_subquestion' => false,
                'parent_client_key' => '',
                'condition_value' => '',
                'is_required' => false,
                'is_technical_description_required' => false,
                'is_image_required' => false,
                'required_images_count' => 1,
                'has_help' => false,
                'help_content' => '',
                'is_collapsed' => false,
                'options' => [],
                'answer_procedure_map' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => (string) Str::uuid(),
                'sequence' => 2,
                'section_name' => 'Hardware',
                'question_text' => 'Subpergunta da 1',
                'question_type' => 'yes_no',
                'is_subquestion' => true,
                'parent_client_key' => $firstClientKey,
                'condition_value' => 'no',
                'is_required' => false,
                'is_technical_description_required' => false,
                'is_image_required' => false,
                'required_images_count' => 1,
                'has_help' => false,
                'help_content' => '',
                'is_collapsed' => false,
                'options' => [],
                'answer_procedure_map' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => (string) Str::uuid(),
                'sequence' => 3,
                'section_name' => 'Sistema',
                'question_text' => 'Pergunta principal 2',
                'question_type' => 'yes_no',
                'is_subquestion' => false,
                'parent_client_key' => '',
                'condition_value' => '',
                'is_required' => false,
                'is_technical_description_required' => false,
                'is_image_required' => false,
                'required_images_count' => 1,
                'has_help' => false,
                'help_content' => '',
                'is_collapsed' => false,
                'options' => [],
                'answer_procedure_map' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $component->call('moveQuestionDown', 0);

        $questions = collect($component->get('questions'))->values();

        $this->assertSame('Pergunta principal 2', data_get($questions, '0.question_text'));
        $this->assertSame('Pergunta principal 1', data_get($questions, '1.question_text'));
        $this->assertSame('Subpergunta da 1', data_get($questions, '2.question_text'));
        $this->assertFalse((bool) data_get($questions, '1.is_subquestion'));
        $this->assertTrue((bool) data_get($questions, '2.is_subquestion'));
        $this->assertSame(data_get($questions, '1.client_key'), data_get($questions, '2.parent_client_key'));
    }
}
