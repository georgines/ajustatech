<?php

namespace Ajustatech\ServiceOrder\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisServiceInterface;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class AnalysisManagement extends Component
{
    public string $title = '';
    public string $mode = 'create';
    public ?string $analysisServiceId = null;

    public string $name = '';
    public string $description = '';
    public mixed $value = null;

    public array $questions = [];
    public array $availableProcedures = [];
    public array $newQuestionDraft = [];
    public ?int $editingQuestionIndex = null;
    public ?int $editingHelpQuestionIndex = null;
    public array $questionHelpDraft = [];

    public function mount(AnalysisServiceInterface $service, ?string $id = null): void
    {
        $this->availableProcedures = ServiceOrderProcedure::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();

        $this->questions = [$this->newQuestionRow(1)];
        $this->resetNewQuestionDraft();
        $this->title = trans('service-order::messages.analysis_service_create_title');

        if (!$id) {
            return;
        }

        $analysis = $service->findAnalysisService($id);

        $this->mode = 'edit';
        $this->analysisServiceId = $analysis->id;
        $this->name = (string) $analysis->name;
        $this->description = (string) ($analysis->description ?? '');
        $this->value = (float) $analysis->value;
        $this->title = trans('service-order::messages.analysis_service_edit_title');

        $mappedQuestions = $analysis->questions
            ->sortBy('sequence')
            ->values()
            ->map(function ($question) {
                $type = (string) $question->question_type;
                $options = $this->normalizeOptions((array) ($question->options_json ?? []), $type);
                $answerMap = $this->normalizeAnswerProcedureMap((array) ($question->answer_procedure_map_json ?? []), $type, $options);

                return [
                    'client_key' => (string) $question->id,
                    'sequence' => (int) $question->sequence,
                    'section_name' => (string) ($question->section_name ?? 'Geral'),
                    'question_text' => (string) $question->question_text,
                    'question_type' => $type,
                    'is_required' => (bool) $question->is_required,
                    'is_technical_description_required' => (bool) $question->is_technical_description_required,
                    'is_image_required' => (bool) $question->is_image_required,
                    'required_images_count' => (int) ($question->required_images_count ?? 1),
                    'has_help' => (bool) ($question->has_help ?? false),
                    'help_content' => (string) ($question->help_content ?? ''),
                    'options' => $options,
                    'answer_procedure_map' => $answerMap,
                ];
            })
            ->all();

        $this->questions = empty($mappedQuestions) ? [$this->newQuestionRow(1)] : $mappedQuestions;
        $this->resequenceQuestions();
    }

    public function resetNewQuestionDraft(): void
    {
        $this->newQuestionDraft = [
            'question_type' => ServiceOrderAnalysisQuestion::TYPE_YES_NO,
            'is_required' => false,
            'is_technical_description_required' => false,
            'is_image_required' => false,
            'required_images_count' => 1,
            'has_help' => false,
        ];
    }

    public function resetQuestionModalState(): void
    {
        $this->editingQuestionIndex = null;
        $this->resetNewQuestionDraft();
    }

    public function resetQuestionHelpModalState(): void
    {
        $this->editingHelpQuestionIndex = null;
        $this->questionHelpDraft = [
            'content' => '',
        ];
    }

    public function addQuestion(): void
    {
        $this->questions[] = $this->newQuestionRow(count($this->questions) + 1);
        $this->resequenceQuestions();
    }

    public function openCreateQuestionModal(?int $afterIndex = null): void
    {
        $this->resetQuestionModalState();
        $this->dispatch('analysis-question-create-open-modal');
    }

    public function createQuestionFromModal(string $type): void
    {
        if (!in_array($type, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            return;
        }

        $isImageRequired = (bool) ($this->newQuestionDraft['is_image_required'] ?? false);
        $requiredImagesCount = (int) ($this->newQuestionDraft['required_images_count'] ?? 1);

        $this->questions[] = $this->newQuestionRow(
            count($this->questions) + 1,
            $type,
            [
                'is_required' => (bool) ($this->newQuestionDraft['is_required'] ?? false),
                'is_technical_description_required' => (bool) ($this->newQuestionDraft['is_technical_description_required'] ?? false),
                'is_image_required' => $isImageRequired,
                'required_images_count' => $isImageRequired ? max(1, min(5, $requiredImagesCount)) : 1,
                'has_help' => (bool) ($this->newQuestionDraft['has_help'] ?? false),
            ]
        );

        $this->resequenceQuestions();
        $this->resetQuestionModalState();
        $this->dispatch('analysis-question-added');
    }

    public function removeQuestion(int $index): void
    {
        if (!isset($this->questions[$index])) {
            return;
        }

        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
        $this->resequenceQuestions();
    }

    public function editQuestion(int $index): void
    {
        if (!isset($this->questions[$index])) {
            return;
        }

        $question = $this->questions[$index];

        $this->editingQuestionIndex = $index;
        $this->newQuestionDraft = [
            'question_type' => (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO),
            'is_required' => (bool) ($question['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($question['is_technical_description_required'] ?? false),
            'is_image_required' => (bool) ($question['is_image_required'] ?? false),
            'required_images_count' => min(5, max(1, (int) ($question['required_images_count'] ?? 1))),
            'has_help' => (bool) ($question['has_help'] ?? false),
        ];

        $this->dispatch('analysis-question-edit-open-modal');
    }

    public function saveQuestionOptionsFromModal(): void
    {
        if ($this->editingQuestionIndex === null || !isset($this->questions[$this->editingQuestionIndex])) {
            return;
        }

        $isImageRequired = (bool) ($this->newQuestionDraft['is_image_required'] ?? false);
        $requiredImagesCount = (int) ($this->newQuestionDraft['required_images_count'] ?? 1);
        $selectedType = (string) ($this->newQuestionDraft['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
        if (!in_array($selectedType, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            $selectedType = ServiceOrderAnalysisQuestion::TYPE_YES_NO;
        }

        $this->questions[$this->editingQuestionIndex]['question_type'] = $selectedType;
        $this->questions[$this->editingQuestionIndex]['is_required'] = (bool) ($this->newQuestionDraft['is_required'] ?? false);
        $this->questions[$this->editingQuestionIndex]['is_technical_description_required'] = (bool) ($this->newQuestionDraft['is_technical_description_required'] ?? false);
        $this->questions[$this->editingQuestionIndex]['is_image_required'] = $isImageRequired;
        $this->questions[$this->editingQuestionIndex]['required_images_count'] = $isImageRequired
            ? min(5, max(1, $requiredImagesCount))
            : 1;
        $this->questions[$this->editingQuestionIndex]['has_help'] = (bool) ($this->newQuestionDraft['has_help'] ?? false);

        if (!($this->questions[$this->editingQuestionIndex]['has_help'] ?? false)) {
            $this->questions[$this->editingQuestionIndex]['help_content'] = '';
        }

        if ($selectedType === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
            $this->questions[$this->editingQuestionIndex]['options'] = [];
            $this->questions[$this->editingQuestionIndex]['answer_procedure_map'] = [
                'yes' => ['procedure_id' => (string) (($this->questions[$this->editingQuestionIndex]['answer_procedure_map']['yes']['procedure_id'] ?? ''))],
                'no' => ['procedure_id' => (string) (($this->questions[$this->editingQuestionIndex]['answer_procedure_map']['no']['procedure_id'] ?? ''))],
            ];
        } else {
            $existingOptions = array_values((array) ($this->questions[$this->editingQuestionIndex]['options'] ?? []));
            if (empty($existingOptions)) {
                $existingOptions = [
                    ['key' => (string) Str::uuid(), 'label' => '', 'procedure_id' => ''],
                ];
            }
            $this->questions[$this->editingQuestionIndex]['options'] = $existingOptions;

            $normalizedMap = [];
            foreach ($existingOptions as $option) {
                $key = (string) ($option['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $normalizedMap[$key] = [
                    'procedure_id' => (string) (($option['procedure_id'] ?? '')),
                ];
            }
            $this->questions[$this->editingQuestionIndex]['answer_procedure_map'] = $normalizedMap;
        }

        $this->dispatch('analysis-question-options-saved');
        $this->resetQuestionModalState();
    }

    public function setQuestionTypeOnEdit(string $type): void
    {
        if ($this->editingQuestionIndex === null) {
            return;
        }

        if (!in_array($type, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            return;
        }

        $this->newQuestionDraft['question_type'] = $type;
    }

    public function openQuestionHelpModal(int $index): void
    {
        if (!isset($this->questions[$index]) || !($this->questions[$index]['has_help'] ?? false)) {
            return;
        }

        $this->editingHelpQuestionIndex = $index;
        $this->questionHelpDraft = [
            'content' => (string) ($this->questions[$index]['help_content'] ?? ''),
        ];

        $this->dispatch('analysis-question-help-open-modal');
    }

    public function saveQuestionHelpFromModal(): void
    {
        if ($this->editingHelpQuestionIndex === null || !isset($this->questions[$this->editingHelpQuestionIndex])) {
            return;
        }

        $content = $this->sanitizeText((string) ($this->questionHelpDraft['content'] ?? ''), 2000);
        $this->questions[$this->editingHelpQuestionIndex]['help_content'] = $content;

        $this->dispatch('analysis-question-help-saved');
        $this->resetQuestionHelpModalState();
    }

    public function moveQuestionUp(int $index): void
    {
        if ($index <= 0 || !isset($this->questions[$index], $this->questions[$index - 1])) {
            return;
        }

        $current = $this->questions[$index];
        $this->questions[$index] = $this->questions[$index - 1];
        $this->questions[$index - 1] = $current;
        $this->questions = array_values($this->questions);
        $this->resequenceQuestions();
    }

    public function moveQuestionDown(int $index): void
    {
        if (!isset($this->questions[$index], $this->questions[$index + 1])) {
            return;
        }

        $current = $this->questions[$index];
        $this->questions[$index] = $this->questions[$index + 1];
        $this->questions[$index + 1] = $current;
        $this->questions = array_values($this->questions);
        $this->resequenceQuestions();
    }

    public function addOption(int $index): void
    {
        if (!isset($this->questions[$index])) {
            return;
        }

        if (count((array) ($this->questions[$index]['options'] ?? [])) >= 8) {
            return;
        }

        $this->questions[$index]['options'][] = [
            'key' => (string) Str::uuid(),
            'label' => '',
            'procedure_id' => '',
        ];
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        if (!isset($this->questions[$questionIndex]['options'][$optionIndex])) {
            return;
        }

        $removedOptionKey = (string) ($this->questions[$questionIndex]['options'][$optionIndex]['key'] ?? '');
        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);

        if ($removedOptionKey !== '') {
            unset($this->questions[$questionIndex]['answer_procedure_map'][$removedOptionKey]);
        }
    }

    public function save(AnalysisServiceInterface $service)
    {
        $this->sanitizeInputs();
        $this->resequenceQuestions();

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'value' => 'required|numeric|min:0|max:999999.99',
            'questions' => 'required|array|min:1',
        ], [], [
            'name' => trans('service-order::messages.analysis_service_name'),
            'description' => trans('service-order::messages.analysis_service_description'),
            'value' => trans('service-order::messages.analysis_service_value'),
            'questions' => trans('service-order::messages.analysis_questions'),
        ]);

        if (!$this->validateQuestionsStructure()) {
            return null;
        }

        $payload = [
            'name' => $this->name,
            'description' => $this->nullableValue($this->description),
            'value' => round((float) $this->value, 2),
        ];

        $questionPayload = collect($this->questions)
            ->map(function (array $question) {
                $type = (string) $question['question_type'];
                $options = array_values($question['options'] ?? []);
                $answerMap = $this->buildAnswerProcedureMap($type, $options, (array) ($question['answer_procedure_map'] ?? []));

                return [
                    'client_key' => $question['client_key'],
                    'sequence' => (int) $question['sequence'],
                    'section_name' => $question['section_name'],
                    'question_text' => $question['question_text'],
                    'question_type' => $type,
                    'is_required' => (bool) $question['is_required'],
                    'technical_description' => null,
                    'is_technical_description_required' => (bool) $question['is_technical_description_required'],
                    'images_json' => null,
                    'is_image_required' => (bool) $question['is_image_required'],
                    'required_images_count' => (bool) $question['is_image_required']
                        ? (int) $question['required_images_count']
                        : null,
                    'has_help' => (bool) ($question['has_help'] ?? false),
                    'help_content' => (bool) ($question['has_help'] ?? false)
                        ? $this->nullableValue($question['help_content'] ?? '')
                        : null,
                    'options_json' => $type === ServiceOrderAnalysisQuestion::TYPE_SELECT ? $options : null,
                    'parent_client_key' => null,
                    'condition_value' => null,
                    'answer_procedure_map_json' => $answerMap,
                ];
            })
            ->all();

        if ($this->mode === 'edit' && $this->analysisServiceId) {
            $service->updateAnalysisService($this->analysisServiceId, $payload, $questionPayload);
        } else {
            $service->createAnalysisService($payload, $questionPayload);
        }

        return redirect()->route('service-order-analyses-show');
    }

    private function validateQuestionsStructure(): bool
    {
        foreach ($this->questions as $index => $question) {
            $basePath = "questions.{$index}";
            $questionType = $question['question_type'] ?? '';
            $options = array_values(array_filter(
                (array) ($question['options'] ?? []),
                fn ($option) => trim((string) ($option['label'] ?? '')) !== ''
            ));

            if (trim((string) ($question['section_name'] ?? '')) === '') {
                $this->addError("{$basePath}.section_name", trans('validation.required', [
                    'attribute' => trans('service-order::messages.analysis_section_name'),
                ]));
            }

            if (trim((string) ($question['question_text'] ?? '')) === '') {
                $this->addError("{$basePath}.question_text", trans('validation.required', [
                    'attribute' => trans('service-order::messages.analysis_question_text'),
                ]));
            }

            if (!in_array($questionType, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
                $this->addError("{$basePath}.question_type", trans('validation.in', [
                    'attribute' => trans('service-order::messages.analysis_question_type'),
                ]));
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT && empty($options)) {
                $this->addError("{$basePath}.options", trans('service-order::messages.analysis_question_options_required'));
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT && count($options) > 8) {
                $this->addError("{$basePath}.options", trans('service-order::messages.analysis_question_options_max'));
            }

            if ($question['is_image_required'] ?? false) {
                $requiredCount = (int) ($question['required_images_count'] ?? 0);
                if ($requiredCount < 1 || $requiredCount > 5) {
                    $this->addError("{$basePath}.required_images_count", trans('service-order::messages.analysis_required_images_count_invalid'));
                }
            }
        }

        return !$this->getErrorBag()->isNotEmpty();
    }

    private function sanitizeInputs(): void
    {
        $this->name = $this->sanitizeText($this->name, 255);
        $this->description = $this->sanitizeText($this->description, 1000);

        foreach ($this->questions as $index => $question) {
            $type = (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
            $options = collect((array) ($question['options'] ?? []))
                ->map(fn ($option) => [
                    'key' => (string) ($option['key'] ?? Str::uuid()),
                    'label' => $this->sanitizeText((string) ($option['label'] ?? ''), 120),
                    'procedure_id' => (string) ($option['procedure_id'] ?? ''),
                ])
                ->filter(fn (array $option) => $option['label'] !== '')
                ->values()
                ->all();

            $this->questions[$index]['sequence'] = max(1, (int) ($question['sequence'] ?? ($index + 1)));
            $this->questions[$index]['section_name'] = $this->sanitizeText((string) ($question['section_name'] ?? 'Geral'), 120);
            $this->questions[$index]['question_text'] = $this->sanitizeText((string) ($question['question_text'] ?? ''), 500);
            $this->questions[$index]['question_type'] = $type;
            $this->questions[$index]['is_required'] = (bool) ($question['is_required'] ?? false);
            $this->questions[$index]['is_technical_description_required'] = (bool) ($question['is_technical_description_required'] ?? false);
            $this->questions[$index]['is_image_required'] = (bool) ($question['is_image_required'] ?? false);
            $this->questions[$index]['required_images_count'] = min(5, max(1, (int) ($question['required_images_count'] ?? 1)));
            $this->questions[$index]['has_help'] = (bool) ($question['has_help'] ?? false);
            $this->questions[$index]['help_content'] = $this->sanitizeText((string) ($question['help_content'] ?? ''), 2000);
            $this->questions[$index]['options'] = $options;
            $this->questions[$index]['answer_procedure_map'] = (array) ($question['answer_procedure_map'] ?? []);
        }
    }

    private function normalizeOptions(array $options, string $questionType): array
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

    private function normalizeAnswerProcedureMap(array $answerMap, string $questionType, array $options): array
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

    private function buildAnswerProcedureMap(string $questionType, array $options, array $answerMap): array
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

    private function resequenceQuestions(): void
    {
        foreach ($this->questions as $index => $question) {
            $this->questions[$index]['sequence'] = $index + 1;
        }
    }

    private function sanitizeText(?string $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    private function nullableValue(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function newQuestionRow(int $sequence, ?string $type = null, array $flags = []): array
    {
        $questionType = $type ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO;

        return [
            'client_key' => (string) Str::uuid(),
            'sequence' => $sequence,
            'section_name' => 'Geral',
            'question_text' => '',
            'question_type' => $questionType,
            'is_required' => (bool) ($flags['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($flags['is_technical_description_required'] ?? false),
            'is_image_required' => (bool) ($flags['is_image_required'] ?? false),
            'required_images_count' => min(5, max(1, (int) ($flags['required_images_count'] ?? 1))),
            'has_help' => (bool) ($flags['has_help'] ?? false),
            'help_content' => '',
            'options' => $questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT ? [
                ['key' => (string) Str::uuid(), 'label' => '', 'procedure_id' => ''],
            ] : [],
            'answer_procedure_map' => [
                'yes' => ['procedure_id' => ''],
                'no' => ['procedure_id' => ''],
            ],
        ];
    }

    public function render()
    {
        return view('service-order::livewire.analysis.analysis-management');
    }
}
