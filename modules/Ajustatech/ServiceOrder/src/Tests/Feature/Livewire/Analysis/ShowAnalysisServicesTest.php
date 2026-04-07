<?php

namespace Ajustatech\ServiceOrder\Tests\Feature\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Livewire\Analysis\ShowAnalysisServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ShowAnalysisServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_uses_single_select_query(): void
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

        $queries = $this->countDmlQueries(fn () => Livewire::test(ShowAnalysisServices::class));

        $this->assertCount(1, $queries);
        $this->assertSame(['select'], $queries);
    }

    public function test_delete_analysis_service_uses_single_delete_query(): void
    {
        $service = ServiceOrderAnalysisService::createWithQuestions([
            'id' => (string) Str::uuid(),
            'name' => 'Analise Delete Lista',
            'description' => 'x',
            'value' => 85.00,
        ], [
            [
                'client_key' => 'd1',
                'sequence' => 1,
                'section_name' => 'Secao',
                'question_text' => 'Pergunta D1',
                'question_type' => 'yes_no',
                'is_required' => true,
                'answer_procedure_map_json' => [
                    'yes' => ['procedure_id' => ''],
                    'no' => ['procedure_id' => ''],
                ],
            ],
        ]);

        $component = Livewire::test(ShowAnalysisServices::class);
        $queries = $this->countDmlQueries(fn () => $component->call('deleteAnalysisService', $service->id));

        $this->assertCount(1, $queries);
        $this->assertSame(['delete'], $queries);
        $this->assertDatabaseMissing('service_order_analysis_services', [
            'id' => $service->id,
        ]);
        $this->assertDatabaseMissing('service_order_analysis_questions', [
            'analysis_service_id' => $service->id,
        ]);
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
            ->filter(fn (string $operation) => in_array($operation, ['select', 'insert', 'update', 'delete'], true))
            ->values()
            ->all();
    }
}
