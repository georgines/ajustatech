<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderAnalysisQuestionFactory extends Factory
{
    protected $model = ServiceOrderAnalysisQuestion::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'analysis_service_id' => ServiceOrderAnalysisService::factory(),
            'parent_question_id' => null,
            'sequence' => 1,
            'section_name' => 'Geral',
            'question_text' => 'O SSD esta funcionando?',
            'question_type' => ServiceOrderAnalysisQuestion::TYPE_YES_NO,
            'is_required' => true,
            'technical_description' => $this->faker->sentence(),
            'is_technical_description_required' => false,
            'images_json' => [],
            'is_image_required' => false,
            'required_images_count' => null,
            'options_json' => null,
            'condition_value' => null,
            'answer_procedure_map_json' => [
                'yes' => ['procedure_id' => ''],
                'no' => ['procedure_id' => ''],
            ],
        ];
    }

    public function yesNo(int $sequence = 1): self
    {
        return $this->state(fn () => [
            'sequence' => $sequence,
            'question_type' => ServiceOrderAnalysisQuestion::TYPE_YES_NO,
            'options_json' => null,
            'condition_value' => null,
            'answer_procedure_map_json' => [
                'yes' => ['procedure_id' => ''],
                'no' => ['procedure_id' => ''],
            ],
        ]);
    }

    public function select(int $sequence = 1): self
    {
        return $this->state(fn () => [
            'sequence' => $sequence,
            'question_type' => ServiceOrderAnalysisQuestion::TYPE_SELECT,
            'options_json' => [
                ['key' => 'opt-1', 'label' => 'Opcao 1', 'procedure_id' => ''],
                ['key' => 'opt-2', 'label' => 'Opcao 2', 'procedure_id' => ''],
            ],
            'answer_procedure_map_json' => [
                'opt-1' => ['procedure_id' => ''],
                'opt-2' => ['procedure_id' => ''],
            ],
        ]);
    }
}
