<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisSection;
use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisSectionFactory extends Factory
{
    protected $model = AnalysisSection::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'analysis_type_id' => AnalysisType::factory(),
            'name' => $this->faker->randomElement(['Inspecao visual', 'Testes eletricos', 'Testes de interface']),
            'sort_order' => $this->faker->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}

