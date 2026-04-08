<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
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

    public function test_save_create_uses_two_dml_queries(): void
    {
        $component = Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise de notebook')
            ->set('description', 'Checklist tecnico completo')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.sequence', 1)
            ->set('questions.0.question_text', 'O SSD esta funcionando?')
            ->set('questions.0.question_type', 'yes_no')
            ->set('questions.0.answer_procedure_map.no.procedure_id', '');

        $queries = $this->countDmlQueries(fn () => $component->call('save'));

        $this->assertCount(2, $queries);
        $this->assertSame(['insert', 'insert'], $queries);
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
            ->assertSet('questions.1.is_collapsed', ! $secondBefore);

        $component
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $reloaded = ServiceOrderAnalysisService::findWithQuestionsOrFail($service->id);
        $questions = $reloaded->questions->values();

        $this->assertSame($firstBefore, (bool) ($questions[0]->is_collapsed ?? false));
        $this->assertSame(! $secondBefore, (bool) ($questions[1]->is_collapsed ?? false));
    }

    public function test_toggle_question_collapse_does_not_write_to_database_until_save(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Colapso',
            'description' => 'Teste de persistencia tardia',
            'value' => 100.00,
        ], [
            [
                'client_key' => 'c1',
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta 1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'is_collapsed' => false,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $component = Livewire::test(AnalysisManagement::class, ['id' => $service->id]);

        $queries = $this->countRelevantQueries(fn () => $component->call('toggleQuestionCollapseByIndex', 0));

        $this->assertCount(0, $queries);
        $this->assertDatabaseHas('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta 1',
            'is_collapsed' => 0,
        ]);
    }

    public function test_save_edit_uses_three_relevant_queries(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Edit Query',
            'description' => 'Teste de edicao',
            'value' => 100.00,
        ], [
            [
                'client_key' => 'eq1',
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta 1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'eq2',
                'sequence' => 2,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta 2',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $component = Livewire::test(AnalysisManagement::class, ['id' => $service->id])
            ->set('name', 'Analise Edit Query Atualizada')
            ->set('questions.0.question_text', 'Pergunta 1 atualizada');

        $queries = $this->countRelevantQueries(fn () => $component->call('save'));

        $this->assertCount(3, $queries);
        $this->assertSame(['update', 'delete', 'insert'], $queries);
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
                'label' => 'Opcao '.$index,
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

    public function test_removing_a_main_question_also_removes_its_subquestions(): void
    {
        $component = Livewire::test(AnalysisManagement::class)
            ->set('questions', [
                [
                    'client_key' => (string) Str::uuid(),
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
                    'parent_client_key' => 'parent-1',
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

        $component
            ->call('confirmRemoveQuestion', 0)
            ->assertDispatched('confirmation');

        $component->call('removeQuestionConfirmed', 0);

        $questions = collect($component->get('questions'))->values();

        $this->assertCount(1, $questions);
        $this->assertSame('Pergunta principal 2', data_get($questions, '0.question_text'));
    }

    public function test_save_rejects_questions_without_section_name(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise sem secao')
            ->set('value', '90.00')
            ->set('questions.0.section_name', '')
            ->set('questions.0.question_text', 'Pergunta sem secao')
            ->call('save')
            ->assertHasErrors(['questions.0.section_name']);
    }

    public function test_save_rejects_questions_without_question_text(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise sem texto')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', '')
            ->call('save')
            ->assertHasErrors(['questions.0.question_text']);
    }

    public function test_save_rejects_invalid_question_type(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise tipo invalido')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta com tipo invalido')
            ->set('questions.0.question_type', 'invalid_type')
            ->call('save')
            ->assertHasErrors(['questions.0.question_type']);
    }

    private function countDmlQueries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $callback();
        } finally {
            $queryLog = DB::getQueryLog();
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        return collect($queryLog)
            ->map(fn (array $entry) => strtolower((string) ($entry['query'] ?? '')))
            ->filter(fn (string $query) => str_contains($query, 'service_order_analysis_services') || str_contains($query, 'service_order_analysis_questions'))
            ->values()
            ->map(fn (string $query) => strtolower((string) preg_replace('/\s+.*/', '', ltrim($query))))
            ->filter(fn (string $operation) => in_array($operation, ['insert', 'update', 'delete'], true))
            ->values()
            ->all();
    }

    private function countRelevantQueries(callable $callback): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $callback();
        } finally {
            $queryLog = DB::getQueryLog();
            DB::disableQueryLog();
            DB::flushQueryLog();
        }

        return collect($queryLog)
            ->map(fn (array $entry) => strtolower((string) ($entry['query'] ?? '')))
            ->filter(fn (string $query) => str_contains($query, 'service_order_analysis_services') || str_contains($query, 'service_order_analysis_questions'))
            ->values()
            ->map(fn (string $query) => strtolower((string) preg_replace('/\s+.*/', '', ltrim($query))))
            ->filter(fn (string $operation) => in_array($operation, ['select', 'insert', 'update', 'delete'], true))
            ->values()
            ->all();
    }

    public function test_save_rejects_invalid_required_images_count(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise imagens invalidas')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta com imagens invalidas')
            ->set('questions.0.is_image_required', true)
            ->set('questions.0.required_images_count', 6)
            ->call('save')
            ->assertHasErrors(['questions.0.required_images_count']);
    }

    public function test_save_rejects_first_question_as_subquestion(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise primeira subpergunta')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Subpergunta invalida')
            ->set('questions.0.is_subquestion', true)
            ->set('questions.0.condition_value', 'no')
            ->call('save')
            ->assertHasErrors(['questions.0.is_subquestion']);
    }

    public function test_save_rejects_subquestion_without_condition_value(): void
    {
        $component = Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise subpergunta sem condicao')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta pai')
            ->set('questions.0.question_type', 'yes_no');

        $parentClientKey = (string) data_get($component->get('questions'), '0.client_key');

        $component
            ->set('questions', [
                $this->makeYesNoQuestion($parentClientKey, 1, 'Hardware', 'Pergunta pai'),
                $this->makeYesNoQuestion((string) Str::uuid(), 2, 'Hardware', 'Subpergunta sem condicao', [
                    'is_subquestion' => true,
                    'parent_client_key' => $parentClientKey,
                    'condition_value' => '',
                ]),
            ])
            ->call('save')
            ->assertHasErrors(['questions.1.condition_value']);
    }

    public function test_save_rejects_subquestion_with_invalid_condition_value(): void
    {
        $component = Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise subpergunta com condicao invalida')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta pai')
            ->set('questions.0.question_type', 'yes_no');

        $parentClientKey = (string) data_get($component->get('questions'), '0.client_key');

        $component
            ->set('questions', [
                $this->makeYesNoQuestion($parentClientKey, 1, 'Hardware', 'Pergunta pai'),
                $this->makeYesNoQuestion((string) Str::uuid(), 2, 'Hardware', 'Subpergunta com condicao invalida', [
                    'is_subquestion' => true,
                    'parent_client_key' => $parentClientKey,
                    'condition_value' => 'talvez',
                ]),
            ])
            ->call('save')
            ->assertHasErrors(['questions.1.condition_value']);
    }

    public function test_save_accepts_valid_yes_no_procedure_ids_and_persists_them(): void
    {
        $yesProcedure = ServiceOrderProcedure::factory()->create();
        $noProcedure = ServiceOrderProcedure::factory()->create();

        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise com procedures validos yes no')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Hardware')
            ->set('questions.0.question_text', 'Pergunta com procedures validos')
            ->set('questions.0.question_type', 'yes_no')
            ->set('questions.0.answer_procedure_map.yes.procedure_id', $yesProcedure->id)
            ->set('questions.0.answer_procedure_map.no.procedure_id', $noProcedure->id)
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $question = DB::table('service_order_analysis_questions')
            ->where('question_text', 'Pergunta com procedures validos')
            ->first();

        $this->assertNotNull($question);
        $this->assertStringContainsString((string) $yesProcedure->id, (string) $question->answer_procedure_map_json);
        $this->assertStringContainsString((string) $noProcedure->id, (string) $question->answer_procedure_map_json);
    }

    public function test_save_accepts_valid_select_option_procedure_ids_and_persists_them(): void
    {
        $procedureA = ServiceOrderProcedure::factory()->create();
        $procedureB = ServiceOrderProcedure::factory()->create();

        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise com procedures validos select')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Sistema')
            ->set('questions.0.question_text', 'Qual componente falhou?')
            ->set('questions.0.question_type', 'select')
            ->set('questions.0.options', [
                ['key' => 'opt-1', 'label' => 'SSD', 'procedure_id' => (string) $procedureA->id],
                ['key' => 'opt-2', 'label' => 'RAM', 'procedure_id' => (string) $procedureB->id],
            ])
            ->call('save')
            ->assertRedirect(route('service-order-analyses-show'));

        $question = DB::table('service_order_analysis_questions')
            ->where('question_text', 'Qual componente falhou?')
            ->first();

        $this->assertNotNull($question);
        $this->assertStringContainsString((string) $procedureA->id, (string) $question->answer_procedure_map_json);
        $this->assertStringContainsString((string) $procedureB->id, (string) $question->answer_procedure_map_json);
    }

    public function test_save_rejects_unknown_procedure_ids_in_select_options(): void
    {
        Livewire::test(AnalysisManagement::class)
            ->set('name', 'Analise select com procedure invalido')
            ->set('value', '90.00')
            ->set('questions.0.section_name', 'Sistema')
            ->set('questions.0.question_text', 'Qual componente falhou?')
            ->set('questions.0.question_type', 'select')
            ->set('questions.0.options', [
                ['key' => 'opt-1', 'label' => 'SSD', 'procedure_id' => (string) Str::uuid()],
            ])
            ->call('save')
            ->assertHasErrors(['questions.0.answer_procedure_map']);
    }

    private function makeYesNoQuestion(string $clientKey, int $sequence, string $sectionName, string $questionText, array $overrides = []): array
    {
        return array_replace_recursive([
            'client_key' => $clientKey,
            'sequence' => $sequence,
            'section_name' => $sectionName,
            'question_text' => $questionText,
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
        ], $overrides);
    }
}
