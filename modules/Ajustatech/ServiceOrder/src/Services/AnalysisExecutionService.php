<?php

namespace Ajustatech\ServiceOrder\Services;

use Ajustatech\ServiceOrder\Database\Models\AnalysisConsequence;
use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisAttachment;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisComplementaryResponse;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisResponse;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderTechnicalFinding;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnalysisExecutionService
{
    public function createAnalysisServiceInstance(
        ServiceOrder|string $order,
        string $analysisTypeId,
        ?int $technicianId = null,
        ?string $initialNotes = null
    ): ServiceOrderAnalysisService {
        $order = $order instanceof ServiceOrder ? $order : ServiceOrder::findOrFailById($order);
        $analysisType = AnalysisType::query()
            ->whereKey($analysisTypeId)
            ->where('is_active', true)
            ->with([
                'sections' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'sections.questions' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'sections.questions.options' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'sections.questions.complementaryFields' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ])
            ->firstOrFail();

        return DB::transaction(function () use ($analysisType, $order, $technicianId, $initialNotes): ServiceOrderAnalysisService {
            $analysisService = ServiceOrderAnalysisService::query()->create([
                'service_order_id' => $order->id,
                'analysis_type_id' => $analysisType->id,
                'analysis_type_snapshot' => [
                    'id' => $analysisType->id,
                    'name' => $analysisType->name,
                    'slug' => $analysisType->slug,
                    'description' => $analysisType->description,
                ],
                'technician_id' => $technicianId,
                'initial_notes' => filled($initialNotes) ? trim((string) $initialNotes) : null,
                'status' => 'pending',
            ]);

            foreach ($analysisType->sections as $section) {
                $instanceSection = $analysisService->sections()->create([
                    'source_section_id' => $section->id,
                    'name' => $section->name,
                    'sort_order' => $section->sort_order,
                ]);

                foreach ($section->questions as $question) {
                    $instanceQuestion = $analysisService->questions()->create([
                        'service_order_analysis_section_id' => $instanceSection->id,
                        'source_question_id' => $question->id,
                        'question_code' => $question->code,
                        'prompt' => $question->prompt,
                        'help_text' => $question->help_text,
                        'technician_note_label' => $question->technician_note_label,
                        'answer_type' => $question->answer_type,
                        'sort_order' => $question->sort_order,
                        'is_required' => (bool) $question->is_required,
                        'is_repeatable' => (bool) $question->is_repeatable,
                        'requires_photo_evidence' => (bool) $question->requires_photo_evidence,
                        'is_active' => (bool) $question->is_active,
                    ]);

                    foreach ($question->options as $option) {
                        $instanceQuestion->options()->create([
                            'source_option_id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'sort_order' => $option->sort_order,
                            'is_active' => (bool) $option->is_active,
                        ]);
                    }

                    foreach ($question->complementaryFields as $field) {
                        $instanceQuestion->complementaryFields()->create([
                            'source_complementary_field_id' => $field->id,
                            'name' => $field->name,
                            'label' => $field->label,
                            'field_type' => $field->field_type,
                            'sort_order' => $field->sort_order,
                            'is_required' => (bool) $field->is_required,
                            'is_active' => (bool) $field->is_active,
                            'configuration' => $field->configuration,
                        ]);
                    }
                }
            }

            return $analysisService->fresh(['sections.questions.options', 'sections.questions.complementaryFields']);
        });
    }

    public function answerQuestion(
        ServiceOrderAnalysisService|string $analysisService,
        string $analysisQuestionId,
        array $payload
    ): ServiceOrderAnalysisResponse {
        $analysisService = $analysisService instanceof ServiceOrderAnalysisService
            ? $analysisService
            : ServiceOrderAnalysisService::query()->findOrFail($analysisService);

        $question = $analysisService->questions()
            ->with(['options', 'complementaryFields'])
            ->findOrFail($analysisQuestionId);

        $answerRaw = Arr::get($payload, 'answer');
        $normalized = $this->normalizeAnswer(
            $question->answer_type,
            $answerRaw,
            $question->options->pluck('value')->all(),
            $question->options->keyBy('value')->all()
        );

        if ((bool) $question->is_required && $normalized['is_empty']) {
            throw ValidationException::withMessages([
                'answer' => 'Esta pergunta eh obrigatoria.',
            ]);
        }

        return DB::transaction(function () use ($analysisService, $question, $payload, $normalized): ServiceOrderAnalysisResponse {
            /** @var ServiceOrderAnalysisResponse $response */
            $response = ServiceOrderAnalysisResponse::query()->updateOrCreate(
                ['service_order_analysis_question_id' => $question->id],
                [
                    'service_order_analysis_service_id' => $analysisService->id,
                    'answered_by_user_id' => Arr::get($payload, 'answered_by_user_id'),
                    'answer_text' => $normalized['answer_text'],
                    'answer_number' => $normalized['answer_number'],
                    'answer_date' => $normalized['answer_date'],
                    'answer_boolean' => $normalized['answer_boolean'],
                    'answer_json' => $normalized['answer_json'],
                    'answered_at' => Carbon::now(),
                ]
            );

            $requiredFields = $this->resolveRequiredComplementaryFields(
                (string) $question->source_question_id,
                $question->complementaryFields->keyBy('id')->all(),
                $normalized
            );

            $complementaryPayload = Arr::get($payload, 'complementary', []);
            $complementaryResponseIds = [];
            foreach ($question->complementaryFields as $field) {
                $fieldPayload = Arr::get($complementaryPayload, $field->id);
                $fieldNormalized = $this->normalizeSimpleFieldValue($field->field_type, $fieldPayload);
                $isRequired = (bool) Arr::get($requiredFields, $field->id, false);

                if ($isRequired && $fieldNormalized['is_empty']) {
                    throw ValidationException::withMessages([
                        'complementary.' . $field->id => 'Campo complementar obrigatorio.',
                    ]);
                }

                $complementaryResponse = ServiceOrderAnalysisComplementaryResponse::query()->updateOrCreate(
                    [
                        'service_order_analysis_response_id' => $response->id,
                        'service_order_analysis_complementary_field_id' => $field->id,
                    ],
                    [
                        'value_text' => $fieldNormalized['value_text'],
                        'value_number' => $fieldNormalized['value_number'],
                        'value_date' => $fieldNormalized['value_date'],
                        'value_boolean' => $fieldNormalized['value_boolean'],
                        'value_json' => $fieldNormalized['value_json'],
                    ]
                );

                $complementaryResponseIds[$field->id] = $complementaryResponse->id;
            }

            $attachmentsPayload = Arr::get($payload, 'attachments', []);
            $this->syncAttachments($question->id, $response->id, $attachmentsPayload, $complementaryResponseIds);
            $this->refreshTechnicalFindings($analysisService->id, $question->id, $normalized);

            if ($analysisService->status === 'pending') {
                $analysisService->update([
                    'status' => 'in_progress',
                    'started_at' => $analysisService->started_at ?? Carbon::now(),
                ]);
            }

            return $response->fresh('complementaryResponses.attachments');
        });
    }

    public function finalizeAnalysisService(ServiceOrderAnalysisService|string $analysisService): ServiceOrderAnalysisService
    {
        $analysisService = $analysisService instanceof ServiceOrderAnalysisService
            ? $analysisService
            : ServiceOrderAnalysisService::query()->findOrFail($analysisService);

        $analysisService->load([
            'questions.options',
            'questions.complementaryFields',
            'questions.response.complementaryResponses',
            'questions.response.attachments',
        ]);

        $pending = [];
        foreach ($analysisService->questions->where('is_active', true) as $question) {
            $response = $question->response;
            if ((bool) $question->is_required && !$response) {
                $pending[] = 'Pergunta sem resposta: ' . $question->prompt;
                continue;
            }

            if (!$response) {
                continue;
            }

            $normalized = [
                'answer_text' => $response->answer_text,
                'answer_number' => $response->answer_number,
                'answer_date' => $response->answer_date,
                'answer_boolean' => $response->answer_boolean,
                'answer_json' => $response->answer_json ?? [],
            ];

            $requiredFields = $this->resolveRequiredComplementaryFields(
                (string) $question->source_question_id,
                $question->complementaryFields->keyBy('id')->all(),
                $normalized
            );

            $responsesByField = $response->complementaryResponses->keyBy('service_order_analysis_complementary_field_id');
            foreach ($question->complementaryFields->where('is_active', true) as $field) {
                $isRequired = (bool) Arr::get($requiredFields, $field->id, false);
                if (!$isRequired) {
                    continue;
                }

                $fieldResponse = $responsesByField->get($field->id);
                if (!$fieldResponse || $this->isComplementaryResponseEmpty($fieldResponse)) {
                    $pending[] = 'Campo obrigatorio pendente: ' . $field->label;
                    continue;
                }

                if (in_array($field->field_type, ['photo', 'file'], true) && $fieldResponse->attachments->isEmpty()) {
                    $pending[] = 'Evidencia obrigatoria pendente: ' . $field->label;
                }
            }
        }

        if (!empty($pending)) {
            throw ValidationException::withMessages([
                'analysis' => 'Nao eh possivel finalizar. Pendencias: ' . implode(' | ', $pending),
            ]);
        }

        $analysisService->update([
            'status' => 'finalized',
            'completed_at' => Carbon::now(),
        ]);

        return $analysisService->fresh();
    }

    private function refreshTechnicalFindings(string $analysisServiceId, string $analysisQuestionId, array $normalizedAnswer): void
    {
        $question = ServiceOrderAnalysisService::query()
            ->findOrFail($analysisServiceId)
            ->questions()
            ->whereKey($analysisQuestionId)
            ->firstOrFail();

        if (!$question->source_question_id) {
            return;
        }

        $consequences = AnalysisConsequence::query()
            ->where('analysis_question_id', $question->source_question_id)
            ->where('is_active', true)
            ->get();

        ServiceOrderTechnicalFinding::query()
            ->where('service_order_analysis_service_id', $analysisServiceId)
            ->where('service_order_analysis_question_id', $analysisQuestionId)
            ->delete();

        foreach ($consequences as $consequence) {
            if (!$this->matchesConsequence($consequence, $normalizedAnswer)) {
                continue;
            }

            ServiceOrderTechnicalFinding::query()->create([
                'service_order_analysis_service_id' => $analysisServiceId,
                'service_order_analysis_question_id' => $analysisQuestionId,
                'description' => $consequence->description,
                'severity' => $consequence->severity,
                'analysis_technical_action_id' => $consequence->analysis_technical_action_id,
                'should_generate_budget' => (bool) $consequence->should_generate_budget,
                'generated_automatically' => true,
                'consequence_snapshot' => [
                    'consequence_id' => $consequence->id,
                    'match_operator' => $consequence->match_operator,
                    'match_value' => $consequence->match_value,
                    'analysis_question_option_id' => $consequence->analysis_question_option_id,
                    'visible_to_technician' => $consequence->visible_to_technician,
                    'recommendation_text' => $consequence->recommendation_text,
                ],
                'status' => 'open',
            ]);
        }
    }

    private function matchesConsequence(AnalysisConsequence $consequence, array $normalizedAnswer): bool
    {
        if ($consequence->analysis_question_option_id) {
            return (string) Arr::get($normalizedAnswer, 'answer_json.selected_source_option_id') === (string) $consequence->analysis_question_option_id;
        }

        $currentValue = Arr::get($normalizedAnswer, 'answer_text')
            ?? Arr::get($normalizedAnswer, 'answer_number')
            ?? Arr::get($normalizedAnswer, 'answer_boolean');
        $expected = $consequence->match_value;

        return match ($consequence->match_operator) {
            'not_equals' => (string) $currentValue !== (string) $expected,
            'contains' => is_string($currentValue) && str_contains(mb_strtolower($currentValue), mb_strtolower((string) $expected)),
            default => (string) $currentValue === (string) $expected,
        };
    }

    private function resolveRequiredComplementaryFields(string $sourceQuestionId, array $instanceFieldsById, array $normalizedAnswer): array
    {
        $requiredByFieldId = collect($instanceFieldsById)
            ->mapWithKeys(fn ($field, $id) => [$id => (bool) $field->is_required])
            ->all();

        if ($sourceQuestionId === '') {
            return $requiredByFieldId;
        }

        $rules = DB::table('analysis_conditional_rules')
            ->where('analysis_question_id', $sourceQuestionId)
            ->where('target_type', 'complementary_field')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($rules as $rule) {
            $targetField = collect($instanceFieldsById)->first(
                fn ($field) => (string) $field->source_complementary_field_id === (string) $rule->target_id
            );
            if (!$targetField) {
                continue;
            }

            $matches = false;
            $selectedSourceOptionId = Arr::get($normalizedAnswer, 'answer_json.selected_source_option_id');
            if ($rule->expected_option_id) {
                $matches = (string) $selectedSourceOptionId === (string) $rule->expected_option_id;
            } else {
                $answerValue = Arr::get($normalizedAnswer, 'answer_text')
                    ?? Arr::get($normalizedAnswer, 'answer_number')
                    ?? Arr::get($normalizedAnswer, 'answer_boolean');

                $matches = match ($rule->operator) {
                    'not_equals' => (string) $answerValue !== (string) $rule->expected_value,
                    'contains' => is_string($answerValue) && str_contains(mb_strtolower($answerValue), mb_strtolower((string) $rule->expected_value)),
                    default => (string) $answerValue === (string) $rule->expected_value,
                };
            }

            if (!$matches) {
                continue;
            }

            if ($rule->effect === 'require') {
                $requiredByFieldId[$targetField->id] = true;
            } elseif ($rule->effect === 'optional') {
                $requiredByFieldId[$targetField->id] = false;
            }
        }

        return $requiredByFieldId;
    }

    private function normalizeAnswer(
        string $answerType,
        mixed $answer,
        array $allowedOptionValues,
        array $optionsByValue = []
    ): array
    {
        $result = [
            'answer_text' => null,
            'answer_number' => null,
            'answer_date' => null,
            'answer_boolean' => null,
            'answer_json' => null,
            'is_empty' => true,
        ];

        if ($answer === null || (is_string($answer) && trim($answer) === '')) {
            return $result;
        }

        if (in_array($answerType, ['single_select', 'yes_no', 'radio'], true)) {
            if (!in_array((string) $answer, $allowedOptionValues, true)) {
                throw ValidationException::withMessages(['answer' => 'Opcao invalida para esta pergunta.']);
            }

            $result['answer_text'] = (string) $answer;
            $result['answer_json'] = [
                'selected_value' => (string) $answer,
                'selected_source_option_id' => data_get($optionsByValue, (string) $answer . '.source_option_id'),
            ];
            $result['is_empty'] = false;

            return $result;
        }

        if ($answerType === 'number') {
            if (!is_numeric($answer)) {
                throw ValidationException::withMessages(['answer' => 'Resposta numerica invalida.']);
            }

            $result['answer_number'] = (float) $answer;
            $result['is_empty'] = false;

            return $result;
        }

        if ($answerType === 'date') {
            $parsed = Carbon::parse((string) $answer)->toDateString();
            $result['answer_date'] = $parsed;
            $result['is_empty'] = false;

            return $result;
        }

        if ($answerType === 'boolean') {
            $result['answer_boolean'] = filter_var($answer, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $result['is_empty'] = $result['answer_boolean'] === null;

            return $result;
        }

        if ($answerType === 'multi_select') {
            $values = collect(is_array($answer) ? $answer : [$answer])
                ->map(fn ($value) => (string) $value)
                ->filter()
                ->values();

            if ($values->isEmpty()) {
                return $result;
            }

            $invalid = $values->first(fn ($value) => !in_array($value, $allowedOptionValues, true));
            if ($invalid !== null) {
                throw ValidationException::withMessages(['answer' => 'Selecao multipla contem opcao invalida.']);
            }

            $result['answer_json'] = ['selected_values' => $values->all()];
            $result['answer_text'] = implode(',', $values->all());
            $result['is_empty'] = false;

            return $result;
        }

        $result['answer_text'] = trim((string) $answer);
        $result['is_empty'] = $result['answer_text'] === '';

        return $result;
    }

    private function normalizeSimpleFieldValue(string $fieldType, mixed $value): array
    {
        $result = [
            'value_text' => null,
            'value_number' => null,
            'value_date' => null,
            'value_boolean' => null,
            'value_json' => null,
            'is_empty' => true,
        ];

        if ($value === null || (is_string($value) && trim($value) === '')) {
            return $result;
        }

        if ($fieldType === 'number' && is_numeric($value)) {
            $result['value_number'] = (float) $value;
            $result['is_empty'] = false;
            return $result;
        }

        if ($fieldType === 'date') {
            $result['value_date'] = Carbon::parse((string) $value)->toDateString();
            $result['is_empty'] = false;
            return $result;
        }

        if ($fieldType === 'boolean') {
            $result['value_boolean'] = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $result['is_empty'] = $result['value_boolean'] === null;
            return $result;
        }

        if ($fieldType === 'json' && is_array($value)) {
            $result['value_json'] = $value;
            $result['is_empty'] = empty($value);
            return $result;
        }

        $result['value_text'] = trim((string) $value);
        $result['is_empty'] = $result['value_text'] === '';

        return $result;
    }

    private function syncAttachments(
        string $questionId,
        string $responseId,
        array $attachmentsPayload,
        array $complementaryResponseIds
    ): void
    {
        foreach ($attachmentsPayload as $fieldId => $files) {
            foreach ((array) $files as $file) {
                if (!Arr::get($file, 'path') || !Arr::get($file, 'original_name') || !Arr::get($file, 'extension')) {
                    throw ValidationException::withMessages([
                        'attachments' => 'Anexo invalido. path, original_name e extension sao obrigatorios.',
                    ]);
                }

                ServiceOrderAnalysisAttachment::query()->create([
                    'service_order_analysis_question_id' => $questionId,
                    'service_order_analysis_response_id' => $responseId,
                    'service_order_analysis_complementary_response_id' => $fieldId !== 'question'
                        ? Arr::get($complementaryResponseIds, (string) $fieldId)
                        : null,
                    'service_order_analysis_complementary_field_id' => $fieldId !== 'question' ? $fieldId : null,
                    'disk' => Arr::get($file, 'disk', 'public'),
                    'path' => Arr::get($file, 'path'),
                    'original_name' => Arr::get($file, 'original_name'),
                    'mime_type' => Arr::get($file, 'mime_type'),
                    'extension' => Arr::get($file, 'extension'),
                    'size' => Arr::get($file, 'size'),
                    'metadata' => Arr::get($file, 'metadata', []),
                ]);
            }
        }
    }

    private function isComplementaryResponseEmpty(ServiceOrderAnalysisComplementaryResponse $response): bool
    {
        return $response->value_text === null
            && $response->value_number === null
            && $response->value_date === null
            && $response->value_boolean === null
            && empty($response->value_json);
    }
}
