<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisQuestion;
use Ajustatech\ServiceOrderOld\Database\Models\AnalysisQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisQuestionOptionFactory extends Factory
{
    protected $model = AnalysisQuestionOption::class;

    public function definition(): array
    {
        $value = $this->faker->randomElement(['sim', 'nao', 'baixo', 'medio', 'alto']);

        return [
            'id' => (string) Str::uuid(),
            'analysis_question_id' => AnalysisQuestion::factory(),
            'label' => ucfirst($value),
            'value' => $value,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}

