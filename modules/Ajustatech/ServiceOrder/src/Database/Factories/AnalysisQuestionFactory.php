<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\AnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\AnalysisSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisQuestionFactory extends Factory
{
    protected $model = AnalysisQuestion::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'analysis_section_id' => AnalysisSection::factory(),
            'code' => 'Q-' . $this->faker->numberBetween(1, 999),
            'prompt' => $this->faker->sentence(5),
            'help_text' => $this->faker->sentence(),
            'technician_note_label' => 'Observacao tecnica',
            'answer_type' => $this->faker->randomElement(['text', 'number', 'single_select', 'yes_no']),
            'sort_order' => $this->faker->numberBetween(1, 20),
            'is_required' => $this->faker->boolean(70),
            'is_repeatable' => false,
            'requires_photo_evidence' => $this->faker->boolean(40),
            'repeat_source_question_id' => null,
            'repeat_limit' => null,
            'is_active' => true,
        ];
    }
}

