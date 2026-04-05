<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisQuestion;
use Ajustatech\ServiceOrderOld\Database\Models\AnalysisQuestionComplementaryField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalysisQuestionComplementaryFieldFactory extends Factory
{
    protected $model = AnalysisQuestionComplementaryField::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement(['observacao_tecnica', 'valor_medido', 'foto_evidencia']);
        $fieldType = match ($name) {
            'valor_medido' => 'number',
            'foto_evidencia' => 'photo',
            default => 'text',
        };

        return [
            'id' => (string) Str::uuid(),
            'analysis_question_id' => AnalysisQuestion::factory(),
            'name' => $name,
            'label' => ucwords(str_replace('_', ' ', $name)),
            'field_type' => $fieldType,
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_required' => $this->faker->boolean(50),
            'is_active' => true,
            'configuration' => $fieldType === 'photo'
                ? ['allowed_extensions' => ['jpg', 'jpeg', 'png']]
                : [],
        ];
    }
}

