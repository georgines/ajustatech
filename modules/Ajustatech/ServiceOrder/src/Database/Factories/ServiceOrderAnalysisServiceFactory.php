<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisService;
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
            'analysis_type_snapshot' => [
                'id' => null,
                'name' => 'Analise de Notebook',
                'slug' => 'analise-de-notebook',
                'description' => 'Snapshot de tipo de analise',
            ],
            'technician_id' => null,
            'initial_notes' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'finalized']),
            'started_at' => null,
            'completed_at' => null,
            'reviewed_at' => null,
        ];
    }
}
