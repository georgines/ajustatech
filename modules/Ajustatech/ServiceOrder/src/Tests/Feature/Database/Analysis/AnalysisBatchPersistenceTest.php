<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Database\Analysis;

use Ajustatech\ServiceOrder\Database\Factories\Analysis\ServiceOrderAnalysisServiceFactory;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalysisBatchPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_services_and_questions_in_batch(): void
    {
        $baseServices = ServiceOrderAnalysisServiceFactory::new()->count(2)->make()->values();

        $items = [
            [
                'service' => array_merge($baseServices[0]->toArray(), [
                    'name' => 'Analise Lote A',
                    'value' => 80.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'a1',
                        'sequence' => 1,
                        'section_name' => 'Hardware',
                        'question_text' => 'Pergunta A1',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'is_collapsed' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                    [
                        'client_key' => 'a2',
                        'parent_client_key' => 'a1',
                        'condition_value' => 'no',
                        'sequence' => 2,
                        'section_name' => 'Hardware',
                        'question_text' => 'Subpergunta A2',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => array_merge($baseServices[1]->toArray(), [
                    'name' => 'Analise Lote B',
                    'value' => 120.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'b1',
                        'sequence' => 1,
                        'section_name' => 'Sistema',
                        'question_text' => 'Pergunta B1',
                        'question_type' => 'select',
                        'is_required' => true,
                        'is_collapsed' => false,
                        'options_json' => [
                            ['key' => 'opt-1', 'label' => 'Opcao 1', 'procedure_id' => ''],
                            ['key' => 'opt-2', 'label' => 'Opcao 2', 'procedure_id' => ''],
                        ],
                        'answer_procedure_map_json' => [
                            'opt-1' => ['procedure_id' => ''],
                            'opt-2' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
        ];

        $created = ServiceOrderAnalysisService::createManyWithQuestions($items);

        $this->assertCount(2, $created);
        $this->assertDatabaseCount('service_order_analysis_services', 2);
        $this->assertDatabaseCount('service_order_analysis_questions', 3);

        $serviceA = ServiceOrderAnalysisService::query()->where('name', 'Analise Lote A')->firstOrFail();
        $this->assertCount(2, $serviceA->questions);

        $mainQuestion = $serviceA->questions->firstWhere('question_text', 'Pergunta A1');
        $subQuestion = $serviceA->questions->firstWhere('question_text', 'Subpergunta A2');

        $this->assertNotNull($mainQuestion);
        $this->assertNotNull($subQuestion);
        $this->assertSame($mainQuestion->id, $subQuestion->parent_question_id);
        $this->assertTrue((bool) $mainQuestion->is_collapsed);
    }

    public function test_can_update_service_replacing_questions_with_batch_insert(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Update',
            'description' => 'Antes',
            'value' => 90.00,
        ], [
            [
                'client_key' => 'u1',
                'sequence' => 1,
                'section_name' => 'Base',
                'question_text' => 'Pergunta antiga',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $service->updateWithQuestions([
            'name' => 'Analise Update',
            'description' => 'Depois',
            'value' => 95.00,
        ], [
            [
                'client_key' => 'u2',
                'sequence' => 1,
                'section_name' => 'Nova',
                'question_text' => 'Pergunta nova 1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'is_collapsed' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'u3',
                'sequence' => 2,
                'section_name' => 'Nova',
                'question_text' => 'Pergunta nova 2',
                'question_type' => 'select',
                'is_required' => true,
                'options_json' => [
                    ['key' => 'opt-a', 'label' => 'A', 'procedure_id' => ''],
                    ['key' => 'opt-b', 'label' => 'B', 'procedure_id' => ''],
                ],
                'answer_procedure_map_json' => [
                    'opt-a' => ['procedure_id' => ''],
                    'opt-b' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $this->assertDatabaseMissing('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta antiga',
        ]);
        $this->assertDatabaseHas('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta nova 1',
            'is_collapsed' => 1,
        ]);
        $this->assertDatabaseHas('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
            'question_text' => 'Pergunta nova 2',
        ]);
    }

    public function test_can_list_and_delete_services_with_model_methods(): void
    {
        ServiceOrderAnalysisService::createManyWithQuestions([
            [
                'service' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Analise Lista 1',
                    'description' => 'x',
                    'value' => 70.00,
                ],
                'questions' => [
                    [
                        'client_key' => 'l1',
                        'sequence' => 1,
                        'section_name' => 'Secao',
                        'question_text' => 'Pergunta L1',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Analise Lista 2',
                    'description' => 'y',
                    'value' => 130.00,
                ],
                'questions' => [
                    [
                        'client_key' => 'l2',
                        'sequence' => 1,
                        'section_name' => 'Secao',
                        'question_text' => 'Pergunta L2',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
        ]);

        $listed = ServiceOrderAnalysisService::listForIndex();
        $this->assertCount(2, $listed);
        $this->assertSame(1, (int) $listed->firstWhere('name', 'Analise Lista 1')->questions_count);

        $toDelete = ServiceOrderAnalysisService::query()->where('name', 'Analise Lista 1')->firstOrFail();
        $toDelete->deleteWithRelations();

        $this->assertDatabaseMissing('service_order_analysis_services', [
            'id' => $toDelete->id,
        ]);
        $this->assertDatabaseMissing('service_order_analysis_questions', [
            'analysis_service_id' => $toDelete->id,
        ]);
    }

    public function test_create_many_with_questions_uses_fixed_number_of_queries(): void
    {
        $baseServices = ServiceOrderAnalysisServiceFactory::new()->count(3)->make()->values();

        $items = [
            [
                'service' => array_merge($baseServices[0]->toArray(), [
                    'name' => 'Analise Query A',
                    'value' => 80.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'qa1',
                        'sequence' => 1,
                        'section_name' => 'Hardware',
                        'question_text' => 'Pergunta QA1',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => array_merge($baseServices[1]->toArray(), [
                    'name' => 'Analise Query B',
                    'value' => 90.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'qb1',
                        'sequence' => 1,
                        'section_name' => 'Sistema',
                        'question_text' => 'Pergunta QB1',
                        'question_type' => 'select',
                        'is_required' => true,
                        'options_json' => [
                            ['key' => 'qb-opt-1', 'label' => 'Opcao 1', 'procedure_id' => ''],
                            ['key' => 'qb-opt-2', 'label' => 'Opcao 2', 'procedure_id' => ''],
                        ],
                        'answer_procedure_map_json' => [
                            'qb-opt-1' => ['procedure_id' => ''],
                            'qb-opt-2' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => array_merge($baseServices[2]->toArray(), [
                    'name' => 'Analise Query C',
                    'value' => 100.00,
                ]),
                'questions' => [
                    [
                        'client_key' => 'qc1',
                        'sequence' => 1,
                        'section_name' => 'Rede',
                        'question_text' => 'Pergunta QC1',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                    [
                        'client_key' => 'qc2',
                        'parent_client_key' => 'qc1',
                        'condition_value' => 'no',
                        'sequence' => 2,
                        'section_name' => 'Rede',
                        'question_text' => 'Subpergunta QC2',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
        ];

        $queries = $this->countDmlQueries(fn () => ServiceOrderAnalysisService::createManyWithQuestions($items));

        $this->assertCount(4, $queries);
        $this->assertSame('insert', $queries[0]);
        $this->assertSame('insert', $queries[1]);
        $this->assertSame('select', $queries[2]);
        $this->assertSame('select', $queries[3]);
    }

    public function test_find_with_questions_or_fail_uses_two_queries_with_eager_loading(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Find',
            'description' => 'x',
            'value' => 75.00,
        ], [
            [
                'client_key' => 'f1',
                'sequence' => 1,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta F1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
            [
                'client_key' => 'f2',
                'sequence' => 2,
                'section_name' => 'Hardware',
                'question_text' => 'Pergunta F2',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $queries = $this->countDmlQueries(fn () => ServiceOrderAnalysisService::findWithQuestionsOrFail($service->id));

        $this->assertCount(2, $queries);
        $this->assertSame(['select', 'select'], $queries);
    }

    public function test_list_for_index_uses_single_query_with_questions_count(): void
    {
        ServiceOrderAnalysisService::createManyWithQuestions([
            [
                'service' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Analise Count 1',
                    'description' => 'x',
                    'value' => 70.00,
                ],
                'questions' => [
                    [
                        'client_key' => 'lc1',
                        'sequence' => 1,
                        'section_name' => 'Secao',
                        'question_text' => 'Pergunta LC1',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
            [
                'service' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Analise Count 2',
                    'description' => 'y',
                    'value' => 90.00,
                ],
                'questions' => [
                    [
                        'client_key' => 'lc2',
                        'sequence' => 1,
                        'section_name' => 'Secao',
                        'question_text' => 'Pergunta LC2',
                        'question_type' => 'yes_no',
                        'is_required' => true,
                        'answer_procedure_map_json' => [
                            'yes' => ['procedure_id' => ''],
                            'no' => ['procedure_id' => ''],
                        ],
                    ],
                ],
            ],
        ]);

        $queries = $this->countDmlQueries(fn () => ServiceOrderAnalysisService::listForIndex());

        $this->assertCount(1, $queries);
        $this->assertSame(['select'], $queries);
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
            ->map(fn (array $entry) => strtolower((string) preg_replace('/\s+.*/', '', ltrim((string) ($entry['query'] ?? '')))))
            ->filter(fn (string $operation) => in_array($operation, ['select', 'insert', 'update', 'delete'], true))
            ->values()
            ->all();
    }
}
