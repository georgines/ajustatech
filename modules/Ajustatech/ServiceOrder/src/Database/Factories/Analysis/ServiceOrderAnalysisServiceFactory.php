<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderAnalysisServiceFactory extends Factory
{
    protected $model = ServiceOrderAnalysisService::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => 'Analise de notebook ' . $this->faker->unique()->numberBetween(1, 9999),
            'description' => $this->faker->sentence(8),
            'value' => $this->faker->randomFloat(2, 50, 200),
            'progress_percentage' => 0,
            'last_answered_question_sequence' => null,
            'is_completed' => false,
        ];
    }
}
