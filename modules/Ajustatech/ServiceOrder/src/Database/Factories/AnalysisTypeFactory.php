<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisTypeFactory extends Factory
{
    protected $model = AnalysisType::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Analise de Notebook',
            'Analise de Computador',
            'Analise de Monitor',
        ]);

        return [
            'id' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower((string) Str::uuid()),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}

