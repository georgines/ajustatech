<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderAnalysisServiceFactory extends Factory
{
    protected $model = ServiceOrderAnalysisService::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_order_id' => ServiceOrder::factory(),
            'analysis_type_id' => AnalysisType::factory(),
            'analysis_type_snapshot' => fn (array $attributes) => $this->buildAnalysisTypeSnapshot((string) $attributes['analysis_type_id']),
            'technician_id' => null,
            'initial_notes' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'finalized']),
            'started_at' => null,
            'completed_at' => null,
            'reviewed_at' => null,
        ];
    }

    private function buildAnalysisTypeSnapshot(string $analysisTypeId): array
    {
        $analysisType = AnalysisType::query()->find($analysisTypeId);

        return [
            'id' => $analysisType?->id,
            'name' => $analysisType?->name ?? 'Analise',
            'slug' => $analysisType?->slug ?? 'analise',
            'description' => $analysisType?->description,
        ];
    }
}
