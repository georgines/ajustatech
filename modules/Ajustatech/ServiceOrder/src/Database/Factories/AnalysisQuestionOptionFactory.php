<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\AnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\AnalysisQuestionOption;
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

