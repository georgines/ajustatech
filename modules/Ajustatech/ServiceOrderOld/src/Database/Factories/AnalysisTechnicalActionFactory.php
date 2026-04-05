<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisTechnicalAction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisTechnicalActionFactory extends Factory
{
    protected $model = AnalysisTechnicalAction::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Restauracao de carcaca',
            'Substituicao de carcaca',
            'Troca de conector USB',
            'Reparo de circuito de carga',
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

