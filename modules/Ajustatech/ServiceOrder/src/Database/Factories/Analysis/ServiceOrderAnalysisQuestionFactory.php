<?php

namespace Ajustatech\ServiceOrder\Database\Factories\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderAnalysisQuestionFactory extends Factory
{
    protected $model = ServiceOrderAnalysisQuestion::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
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
}
