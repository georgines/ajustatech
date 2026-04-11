<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisConsequence;
use Ajustatech\ServiceOrderOld\Database\Models\AnalysisQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisConsequenceFactory extends Factory
{
    protected $model = AnalysisConsequence::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'analysis_question_id' => AnalysisQuestion::factory(),
            'analysis_question_option_id' => null,
            'match_operator' => 'equals',
            'match_value' => null,
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'description' => $this->faker->sentence(8),
            'analysis_technical_action_id' => null,
            'should_generate_budget' => true,
            'visible_to_technician' => true,
            'recommendation_text' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}

