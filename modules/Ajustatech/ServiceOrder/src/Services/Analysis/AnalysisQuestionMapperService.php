<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionMapperServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionPayloadServiceInterface;
use Illuminate\Support\Str;

class AnalysisQuestionMapperService implements AnalysisQuestionMapperServiceInterface
{
    public function __construct(
        protected AnalysisQuestionPayloadServiceInterface $payload,
    ) {}

    public function fromPersistedQuestion(object $question): array
    {
        $questionType = (string) ($question->question_type ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
        $optionsJson = (array) ($question->options_json ?? []);
        $answerMapJson = (array) ($question->answer_procedure_map_json ?? []);

        return [
            'client_key' => (string) ($question->id ?? Str::uuid()),
            'sequence' => (int) ($question->sequence ?? 1),
            'section_name' => (string) ($question->section_name ?? 'Geral'),
            'question_text' => (string) ($question->question_text ?? ''),
            'question_type' => $questionType,
            'is_subquestion' => ! empty($question->parent_question_id),
            'parent_client_key' => (string) ($question->parent_question_id ?? ''),
            'condition_value' => (string) ($question->condition_value ?? ''),
            'is_required' => (bool) ($question->is_required ?? false),
            'technical_description' => (string) ($question->technical_description ?? ''),
            'is_technical_description_required' => (bool) ($question->is_technical_description_required ?? false),
            'images_json' => is_array($question->images_json ?? null) ? $question->images_json : [],
            'is_image_required' => (bool) ($question->is_image_required ?? false),
            'required_images_count' => (int) ($question->required_images_count ?? 1),
            'has_help' => (bool) ($question->has_help ?? false),
            'help_content' => (string) ($question->help_content ?? ''),
            'is_collapsed' => (bool) ($question->is_collapsed ?? false),
            'options' => $this->payload->normalizeOptions($optionsJson, $questionType),
            'answer_procedure_map' => $this->payload->normalizeAnswerProcedureMap($answerMapJson, $questionType, $optionsJson),
        ];
    }
}
