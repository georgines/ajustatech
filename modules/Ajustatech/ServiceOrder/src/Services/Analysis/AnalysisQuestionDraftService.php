<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionDraftServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionSanitizerServiceInterface;
use Illuminate\Support\Str;

class AnalysisQuestionDraftService implements AnalysisQuestionDraftServiceInterface
{
    public function __construct(
        protected AnalysisQuestionSanitizerServiceInterface $sanitizer,
    ) {}

    public function newQuestionRow(int $sequence, ?string $type = null, array $flags = []): array
    {
        $questionType = $type ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO;

        return [
            'client_key' => (string) Str::uuid(),
            'sequence' => $sequence,
            'section_name' => 'Geral',
            'question_text' => '',
            'question_type' => $questionType,
            'is_subquestion' => (bool) ($flags['is_subquestion'] ?? false),
            'parent_client_key' => (string) ($flags['parent_client_key'] ?? ''),
            'condition_value' => (string) ($flags['condition_value'] ?? ''),
            'is_required' => (bool) ($flags['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($flags['is_technical_description_required'] ?? false),
            'is_image_required' => (bool) ($flags['is_image_required'] ?? false),
            'required_images_count' => min(5, max(1, (int) ($flags['required_images_count'] ?? 1))),
            'has_help' => (bool) ($flags['has_help'] ?? false),
            'help_content' => '',
            'is_collapsed' => $this->sanitizer->toBool($flags['is_collapsed'] ?? false),
            'options' => $questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT ? [
                ['key' => (string) Str::uuid(), 'label' => '', 'procedure_id' => ''],
            ] : [],
            'answer_procedure_map' => [
                'yes' => ['procedure_id' => ''],
                'no' => ['procedure_id' => ''],
            ],
        ];
    }
}
