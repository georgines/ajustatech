<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionPayloadServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionSanitizerServiceInterface;
use Illuminate\Support\Str;

class AnalysisQuestionPayloadService implements AnalysisQuestionPayloadServiceInterface
{
    public function __construct(
        protected AnalysisQuestionSanitizerServiceInterface $sanitizer,
    ) {}

    public function buildQuestionPayload(array $questions): array
    {
        return collect($questions)
            ->map(function (array $question) {
                $questionType = (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
                $options = $this->normalizeOptions((array) ($question['options'] ?? []), $questionType);
                $answerMap = $this->normalizeAnswerProcedureMap((array) ($question['answer_procedure_map'] ?? []), $questionType, $options);

                return [
                    'client_key' => (string) ($question['client_key'] ?? Str::uuid()),
                    'sequence' => max(1, (int) ($question['sequence'] ?? 1)),
                    'section_name' => $this->sanitizer->sanitizeText((string) ($question['section_name'] ?? 'Geral'), 120),
                    'question_text' => $this->sanitizer->sanitizeText((string) ($question['question_text'] ?? ''), 500),
                    'question_type' => $questionType,
                    'is_required' => (bool) ($question['is_required'] ?? false),
                    'technical_description' => $this->sanitizer->nullableValue($question['technical_description'] ?? null),
                    'is_technical_description_required' => (bool) ($question['is_technical_description_required'] ?? false),
                    'images_json' => ($images = array_values((array) ($question['images_json'] ?? []))) === [] ? null : $images,
                    'is_image_required' => (bool) ($question['is_image_required'] ?? false),
                    'required_images_count' => (bool) ($question['is_image_required'] ?? false)
                        ? min(5, max(1, (int) ($question['required_images_count'] ?? 1)))
                        : null,
                    'has_help' => (bool) ($question['has_help'] ?? false),
                    'help_content' => $this->sanitizer->sanitizeText((string) ($question['help_content'] ?? ''), 2000),
                    'is_collapsed' => $this->sanitizer->toBool($question['is_collapsed'] ?? false),
                    'options_json' => $questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT ? $options : null,
                    'condition_value' => $this->sanitizer->nullableValue($question['condition_value'] ?? null),
                    'answer_procedure_map_json' => $answerMap,
                    'parent_client_key' => (string) ($question['parent_client_key'] ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    public function normalizeOptions(array $options, string $questionType): array
    {
        if ($questionType !== ServiceOrderAnalysisQuestion::TYPE_SELECT) {
            return [];
        }

        return collect($options)
            ->map(function ($option) {
                if (is_string($option)) {
                    return [
                        'key' => (string) Str::uuid(),
                        'label' => $option,
                        'procedure_id' => '',
                    ];
                }

                return [
                    'key' => (string) ($option['key'] ?? Str::uuid()),
                    'label' => (string) ($option['label'] ?? ''),
                    'procedure_id' => (string) ($option['procedure_id'] ?? ''),
                ];
            })
            ->filter(fn (array $option) => trim($option['label']) !== '')
            ->values()
            ->all();
    }

    public function normalizeAnswerProcedureMap(array $answerMap, string $questionType, array $options): array
    {
        if ($questionType === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
            return [
                'yes' => ['procedure_id' => (string) (($answerMap['yes']['procedure_id'] ?? ''))],
                'no' => ['procedure_id' => (string) (($answerMap['no']['procedure_id'] ?? ''))],
            ];
        }

        $map = [];
        foreach ($options as $option) {
            $key = (string) ($option['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $map[$key] = [
                'procedure_id' => (string) (($answerMap[$key]['procedure_id'] ?? $option['procedure_id'] ?? '')),
            ];
        }

        return $map;
    }
}
