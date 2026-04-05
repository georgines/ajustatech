<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderAnalysisServiceFactory extends Factory
{
    protected $model = ServiceOrderAnalysisService::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => 'Analise de notebook',
            'description' => $this->faker->sentence(8),
            'value' => $this->faker->randomFloat(2, 50, 200),
        ];
    }
}

