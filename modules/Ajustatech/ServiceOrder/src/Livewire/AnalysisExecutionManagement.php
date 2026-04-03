<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Services\AnalysisExecutionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class AnalysisExecutionManagement extends Component
{
    use WithFileUploads;

    public string $title = 'Execucao da Analise';
    public string $analysisServiceId;
    public int $currentQuestionIndex = 0;

    /** @var array<int, array<string, mixed>> */
    public array $questionFlow = [];

    /** @var array<string, mixed> */
    public array $answers = [];

    /** @var array<string, array<string, mixed>> */
    public array $complementaryAnswers = [];

    /** @var array<string, array<int, mixed>> */
    public array $questionUploads = [];

    /** @var array<string, array<string, array<int, mixed>>> */
    public array $complementaryUploads = [];

    public function mount(string $id): void
    {
        $analysisService = ServiceOrderAnalysisService::query()
            ->with([
                'order',
                'analysisType',
                'sections' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions.options' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions.complementaryFields' => fn ($query) => $query->orderBy('sort_order'),
                'responses.complementaryResponses',
            ])
            ->findOrFail($id);

        $this->analysisServiceId = $analysisService->id;
        $this->title = 'Execucao da Analise - ' . Arr::get($analysisService->analysis_type_snapshot, 'name', 'Tipo nao identificado');

        $this->questionFlow = $analysisService->sections
            ->flatMap(function ($section) {
                return $section->questions
                    ->where('is_active', true)
                    ->sortBy('sort_order')
                    ->map(fn ($question) => [
                        'section_name' => $section->name,
                        'id' => $question->id,
                        'source_question_id' => $question->source_question_id,
                        'prompt' => $question->prompt,
                        'help_text' => $question->help_text,
                        'technician_note_label' => $question->technician_note_label,
                        'answer_type' => $question->answer_type,
                        'is_required' => (bool) $question->is_required,
                        'requires_photo_evidence' => (bool) $question->requires_photo_evidence,
                        'options' => $question->options->map(fn ($option) => [
                            'id' => $option->id,
                            'source_option_id' => $option->source_option_id,
                            'label' => $option->label,
                            'value' => $option->value,
                        ])->values()->all(),
                        'complementary_fields' => $question->complementaryFields->map(fn ($field) => [
                            'id' => $field->id,
                            'source_complementary_field_id' => $field->source_complementary_field_id,
                            'name' => $field->name,
                            'label' => $field->label,
                            'field_type' => $field->field_type,
                            'is_required' => (bool) $field->is_required,
                        ])->values()->all(),
                    ]);
            })
            ->values()
            ->all();

        $responsesByQuestion = $analysisService->responses->keyBy('service_order_analysis_question_id');
        foreach ($this->questionFlow as $index => $question) {
            $questionId = (string) $question['id'];
            $response = $responsesByQuestion->get($questionId);
            if (!$response) {
                continue;
            }

            $this->answers[$questionId] = $response->answer_text
                ?? $response->answer_number
                ?? $response->answer_date
                ?? $response->answer_boolean
                ?? '';

            $compAnswers = [];
            foreach ($response->complementaryResponses as $compResponse) {
                $compAnswers[(string) $compResponse->service_order_analysis_complementary_field_id] = $compResponse->value_text
                    ?? $compResponse->value_number
                    ?? $compResponse->value_date
                    ?? $compResponse->value_boolean
                    ?? '';
            }
            $this->complementaryAnswers[$questionId] = $compAnswers;

            if ($index === $this->currentQuestionIndex) {
                $this->currentQuestionIndex = min($index + 1, max(count($this->questionFlow) - 1, 0));
            }
        }
    }

    public function previousQuestion(): void
    {
        $this->currentQuestionIndex = max($this->currentQuestionIndex - 1, 0);
    }

    public function nextQuestion(AnalysisExecutionService $service): void
    {
        if (!$this->saveCurrentQuestion($service)) {
            return;
        }

        if ($this->currentQuestionIndex < (count($this->questionFlow) - 1)) {
            $this->currentQuestionIndex++;
        }
    }

    public function finalizeAnalysis(AnalysisExecutionService $service)
    {
        if (!$this->saveCurrentQuestion($service)) {
            return null;
        }

        try {
            $service->finalizeAnalysisService($this->analysisServiceId);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return null;
        }

        return redirect()->route('service-order-analysis-execution-queue');
    }

    public function render()
    {
        $currentQuestion = $this->questionFlow[$this->currentQuestionIndex] ?? null;
        $fieldStates = $currentQuestion ? $this->resolveFieldState($currentQuestion) : [];

        return view('service-order::livewire.analysis-execution-management', [
            'currentQuestion' => $currentQuestion,
            'currentPosition' => $this->currentQuestionIndex + 1,
            'totalQuestions' => count($this->questionFlow),
            'fieldStates' => $fieldStates,
            'isLastQuestion' => $this->currentQuestionIndex >= (count($this->questionFlow) - 1),
        ]);
    }

    private function saveCurrentQuestion(AnalysisExecutionService $service): bool
    {
        $currentQuestion = $this->questionFlow[$this->currentQuestionIndex] ?? null;
        if (!$currentQuestion) {
            return true;
        }

        $questionId = (string) $currentQuestion['id'];
        $answer = Arr::get($this->answers, $questionId);
        $fieldStates = $this->resolveFieldState($currentQuestion);
        $complementaryPayload = [];
        foreach (Arr::get($currentQuestion, 'complementary_fields', []) as $field) {
            $fieldId = (string) $field['id'];
            if (!Arr::get($fieldStates, $fieldId . '.visible', true)) {
                continue;
            }

            $complementaryPayload[$fieldId] = Arr::get($this->complementaryAnswers, $questionId . '.' . $fieldId);
        }

        $attachments = [];
        foreach ((array) Arr::get($this->questionUploads, $questionId, []) as $upload) {
            $path = $upload->store('analysis-services/' . $this->analysisServiceId, 'public');
            $attachments['question'][] = [
                'disk' => 'public',
                'path' => $path,
                'original_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getMimeType(),
                'extension' => strtolower($upload->getClientOriginalExtension()),
                'size' => $upload->getSize(),
            ];
        }

        foreach ((array) Arr::get($this->complementaryUploads, $questionId, []) as $fieldId => $uploads) {
            foreach ((array) $uploads as $upload) {
                $path = $upload->store('analysis-services/' . $this->analysisServiceId, 'public');
                $attachments[$fieldId][] = [
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $upload->getClientOriginalName(),
                    'mime_type' => $upload->getMimeType(),
                    'extension' => strtolower($upload->getClientOriginalExtension()),
                    'size' => $upload->getSize(),
                ];
            }
        }

        try {
            $service->answerQuestion($this->analysisServiceId, $questionId, [
                'answer' => $answer,
                'answered_by_user_id' => auth()->id(),
                'complementary' => $complementaryPayload,
                'attachments' => $attachments,
            ]);
            $this->resetErrorBag();
            return true;
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return false;
        }
    }

    private function resolveFieldState(array $question): array
    {
        $states = collect(Arr::get($question, 'complementary_fields', []))
            ->mapWithKeys(fn (array $field) => [
                (string) $field['id'] => [
                    'visible' => true,
                    'required' => (bool) Arr::get($field, 'is_required', false),
                ],
            ])
            ->all();

        $sourceQuestionId = Arr::get($question, 'source_question_id');
        if (!$sourceQuestionId) {
            return $states;
        }

        $selectedValue = (string) Arr::get($this->answers, (string) Arr::get($question, 'id'));
        $selectedOption = collect(Arr::get($question, 'options', []))->firstWhere('value', $selectedValue);
        $selectedSourceOptionId = Arr::get($selectedOption, 'source_option_id');
        $sourceFieldByInstanceFieldId = collect(Arr::get($question, 'complementary_fields', []))
            ->mapWithKeys(fn (array $field) => [
                (string) $field['id'] => (string) Arr::get($field, 'source_complementary_field_id'),
            ]);

        $rules = DB::table('analysis_conditional_rules')
            ->where('analysis_question_id', $sourceQuestionId)
            ->where('target_type', 'complementary_field')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($rules as $rule) {
            $targetInstanceFieldId = $sourceFieldByInstanceFieldId
                ->search((string) $rule->target_id, true);
            if ($targetInstanceFieldId === false || !isset($states[$targetInstanceFieldId])) {
                continue;
            }

            $matches = false;
            if ($rule->expected_option_id) {
                $matches = (string) $selectedSourceOptionId === (string) $rule->expected_option_id;
            } else {
                $matches = match ($rule->operator) {
                    'not_equals' => $selectedValue !== (string) $rule->expected_value,
                    'contains' => str_contains(mb_strtolower($selectedValue), mb_strtolower((string) $rule->expected_value)),
                    default => $selectedValue === (string) $rule->expected_value,
                };
            }

            if (!$matches) {
                continue;
            }

            if ($rule->effect === 'require') {
                $states[$targetInstanceFieldId]['required'] = true;
                $states[$targetInstanceFieldId]['visible'] = true;
            } elseif ($rule->effect === 'optional') {
                $states[$targetInstanceFieldId]['required'] = false;
            } elseif ($rule->effect === 'show') {
                $states[$targetInstanceFieldId]['visible'] = true;
            } elseif ($rule->effect === 'hide') {
                $states[$targetInstanceFieldId]['visible'] = false;
                $states[$targetInstanceFieldId]['required'] = false;
            }
        }

        return $states;
    }
}
