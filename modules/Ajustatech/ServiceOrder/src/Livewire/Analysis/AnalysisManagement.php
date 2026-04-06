<?php

namespace Ajustatech\ServiceOrder\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('core::layouts.app')]
class AnalysisManagement extends Component
{
    #[Locked]
    public string $title = '';

    #[Locked]
    public string $mode = 'create';

    #[Locked]
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

    public function mount(?string $id = null): void
    {
        $this->availableProcedures = ServiceOrderAnalysisService::listProcedureOptions();

        $this->questions = [$this->newQuestionRow(1)];
        $this->resetNewQuestionDraft();
        $this->title = trans('service-order::messages.analysis_service_create_title');

        if (!$id) {
            return;
        }

        $analysis = ServiceOrderAnalysisService::findWithQuestionsOrFail($id);

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
                    'is_collapsed' => $this->toBool($question->is_collapsed ?? false),
                    'is_subquestion' => $question->parent_question_id !== null,
                    'parent_client_key' => (string) ($question->parent_question_id ?? ''),
                    'condition_value' => (string) ($question->condition_value ?? ''),
                    'options' => $options,
                    'answer_procedure_map' => $answerMap,
                ];
            })
            ->all();

        $this->questions = empty($mappedQuestions) ? [$this->newQuestionRow(1)] : $mappedQuestions;
        $this->resequenceQuestions();
        $this->syncQuestionDependencies();
    }

    public function resetNewQuestionDraft(): void
    {
        $this->newQuestionDraft = [
            'question_type' => ServiceOrderAnalysisQuestion::TYPE_YES_NO,
            'is_subquestion' => false,
            'condition_value' => '',
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
        $isSubquestion = (bool) ($this->newQuestionDraft['is_subquestion'] ?? false);
        $parentIndex = $isSubquestion ? $this->getLastMainQuestionIndex() : null;
        $conditionValue = trim((string) ($this->newQuestionDraft['condition_value'] ?? ''));
        $triggerValues = collect($this->getNewSubquestionTriggerOptions())->pluck('value')->all();

        if ($isSubquestion && ($parentIndex === null || empty($triggerValues))) {
            $this->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_required'));
            return;
        }

        if ($isSubquestion && ($conditionValue === '' || !in_array($conditionValue, $triggerValues, true))) {
            $this->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_invalid'));
            return;
        }

        $newQuestion = $this->newQuestionRow(
            count($this->questions) + 1,
            $type,
            [
                'is_subquestion' => $isSubquestion,
                'parent_client_key' => $isSubquestion
                    ? (string) ($this->questions[$parentIndex]['client_key'] ?? '')
                    : '',
                'condition_value' => $isSubquestion ? $conditionValue : '',
                'is_required' => (bool) ($this->newQuestionDraft['is_required'] ?? false),
                'is_technical_description_required' => (bool) ($this->newQuestionDraft['is_technical_description_required'] ?? false),
                'is_image_required' => $isImageRequired,
                'required_images_count' => $isImageRequired ? max(1, min(5, $requiredImagesCount)) : 1,
                'has_help' => (bool) ($this->newQuestionDraft['has_help'] ?? false),
            ]
        );

        if ($isSubquestion && $parentIndex !== null) {
            $insertIndex = $this->getSubquestionInsertIndexForParent($parentIndex);
            array_splice($this->questions, $insertIndex, 0, [$newQuestion]);
        } else {
            $this->questions[] = $newQuestion;
        }

        $this->resequenceQuestions();
        $this->syncQuestionDependencies();
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
        $this->syncQuestionDependencies();
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
            'is_subquestion' => (bool) ($question['is_subquestion'] ?? false),
            'condition_value' => (string) ($question['condition_value'] ?? ''),
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
        $isSubquestion = (bool) ($this->newQuestionDraft['is_subquestion'] ?? false);
        $conditionValue = trim((string) ($this->newQuestionDraft['condition_value'] ?? ''));
        $triggerValues = collect($this->getSubquestionTriggerOptionsForEdit($this->editingQuestionIndex))->pluck('value')->all();

        $this->questions[$this->editingQuestionIndex]['question_type'] = $selectedType;
        $this->questions[$this->editingQuestionIndex]['is_subquestion'] = $isSubquestion;
        $this->questions[$this->editingQuestionIndex]['condition_value'] = $isSubquestion ? $conditionValue : '';
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

        if ($isSubquestion && ($conditionValue === '' || !in_array($conditionValue, $triggerValues, true))) {
            $this->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_invalid'));
            return;
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

        $this->syncQuestionDependencies();
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
        if (!isset($this->questions[$index])) {
            return;
        }

        $mainStart = $this->resolveMainStartIndex($index);
        if ($mainStart === null) {
            return;
        }

        $mainIndices = $this->getMainQuestionIndexes();
        $mainPosition = array_search($mainStart, $mainIndices, true);
        if ($mainPosition === false || $mainPosition === 0) {
            return;
        }

        $previousMainStart = $mainIndices[$mainPosition - 1];
        $currentMainEnd = $this->getMainBlockEndIndex($mainStart);
        if ($currentMainEnd === null) {
            return;
        }

        $before = array_slice($this->questions, 0, $previousMainStart);
        $previousBlock = array_slice($this->questions, $previousMainStart, $mainStart - $previousMainStart);
        $currentBlock = array_slice($this->questions, $mainStart, $currentMainEnd - $mainStart + 1);
        $after = array_slice($this->questions, $currentMainEnd + 1);

        $this->questions = array_values(array_merge($before, $currentBlock, $previousBlock, $after));
        $this->resequenceQuestions();
        $this->syncQuestionDependencies();
    }

    public function moveQuestionDown(int $index): void
    {
        if (!isset($this->questions[$index])) {
            return;
        }

        $mainStart = $this->resolveMainStartIndex($index);
        if ($mainStart === null) {
            return;
        }

        $mainIndices = $this->getMainQuestionIndexes();
        $mainPosition = array_search($mainStart, $mainIndices, true);
        if ($mainPosition === false || $mainPosition >= (count($mainIndices) - 1)) {
            return;
        }

        $nextMainStart = $mainIndices[$mainPosition + 1];
        $currentMainEnd = $this->getMainBlockEndIndex($mainStart);
        $nextMainEnd = $this->getMainBlockEndIndex($nextMainStart);
        if ($currentMainEnd === null || $nextMainEnd === null) {
            return;
        }

        $before = array_slice($this->questions, 0, $mainStart);
        $currentBlock = array_slice($this->questions, $mainStart, $currentMainEnd - $mainStart + 1);
        $nextBlock = array_slice($this->questions, $nextMainStart, $nextMainEnd - $nextMainStart + 1);
        $after = array_slice($this->questions, $nextMainEnd + 1);

        $this->questions = array_values(array_merge($before, $nextBlock, $currentBlock, $after));
        $this->resequenceQuestions();
        $this->syncQuestionDependencies();
    }

    public function toggleQuestionCollapse(string $clientKey): void
    {
        $normalizedKey = trim($clientKey);
        if ($normalizedKey === '') {
            return;
        }

        foreach ($this->questions as $index => $question) {
            if ((string) ($question['client_key'] ?? '') === $normalizedKey) {
                $this->toggleQuestionCollapseByIndex($index);
                return;
            }
        }
    }

    public function toggleQuestionCollapseByIndex(int $index): void
    {
        if (!isset($this->questions[$index])) {
            return;
        }

        $questionId = (string) ($this->questions[$index]['client_key'] ?? '');
        $newCollapsedState = !$this->toBool($this->questions[$index]['is_collapsed'] ?? false);
        $this->questions[$index]['is_collapsed'] = $newCollapsedState;

        if ($this->mode === 'edit' && $this->analysisServiceId && $questionId !== '') {
            ServiceOrderAnalysisQuestion::query()
                ->where('id', $questionId)
                ->where('analysis_service_id', $this->analysisServiceId)
                ->update([
                    'is_collapsed' => $newCollapsedState,
                ]);
        }
    }

    public function canMoveQuestionUp(int $index): bool
    {
        $mainStart = $this->resolveMainStartIndex($index);
        if ($mainStart === null || $mainStart !== $index) {
            return false;
        }

        $mainIndices = $this->getMainQuestionIndexes();
        $mainPosition = array_search($mainStart, $mainIndices, true);

        return $mainPosition !== false && $mainPosition > 0;
    }

    public function canMoveQuestionDown(int $index): bool
    {
        $mainStart = $this->resolveMainStartIndex($index);
        if ($mainStart === null || $mainStart !== $index) {
            return false;
        }

        $mainIndices = $this->getMainQuestionIndexes();
        $mainPosition = array_search($mainStart, $mainIndices, true);

        return $mainPosition !== false && $mainPosition < (count($mainIndices) - 1);
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

    public function save()
    {
        $this->sanitizeInputs();
        $this->resequenceQuestions();
        $this->syncQuestionDependencies();

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
                    'is_collapsed' => $this->toBool($question['is_collapsed'] ?? false),
                    'options_json' => $type === ServiceOrderAnalysisQuestion::TYPE_SELECT ? $options : null,
                    'parent_client_key' => $this->nullableValue($question['parent_client_key'] ?? ''),
                    'condition_value' => $this->nullableValue($question['condition_value'] ?? ''),
                    'answer_procedure_map_json' => $answerMap,
                ];
            })
            ->all();

        if ($this->mode === 'edit' && $this->analysisServiceId) {
            $service = ServiceOrderAnalysisService::findWithQuestionsOrFail($this->analysisServiceId);
            $service->updateWithQuestions($payload, $questionPayload);
        } else {
            ServiceOrderAnalysisService::createWithQuestions($payload, $questionPayload);
        }

        return redirect()->route('service-order-analyses-show');
    }

    private function validateQuestionsStructure(): bool
    {
        $allowedProcedureIds = $this->getAllowedProcedureIds();

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

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
                foreach (['yes', 'no'] as $answerKey) {
                    $procedureId = trim((string) data_get($question, "answer_procedure_map.{$answerKey}.procedure_id", ''));
                    if ($procedureId !== '' && !isset($allowedProcedureIds[$procedureId])) {
                        $this->addError("{$basePath}.answer_procedure_map", trans('validation.exists', [
                            'attribute' => trans('service-order::messages.analysis_answer_procedures'),
                        ]));
                        break;
                    }
                }
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT) {
                foreach ((array) ($question['options'] ?? []) as $option) {
                    $procedureId = trim((string) ($option['procedure_id'] ?? ''));
                    if ($procedureId !== '' && !isset($allowedProcedureIds[$procedureId])) {
                        $this->addError("{$basePath}.answer_procedure_map", trans('validation.exists', [
                            'attribute' => trans('service-order::messages.analysis_answer_procedures'),
                        ]));
                        break;
                    }
                }
            }

            if ($question['is_image_required'] ?? false) {
                $requiredCount = (int) ($question['required_images_count'] ?? 0);
                if ($requiredCount < 1 || $requiredCount > 5) {
                    $this->addError("{$basePath}.required_images_count", trans('service-order::messages.analysis_required_images_count_invalid'));
                }
            }

            if ($question['is_subquestion'] ?? false) {
                if ($index === 0) {
                    $this->addError("{$basePath}.is_subquestion", trans('service-order::messages.analysis_subquestion_first_invalid'));
                }

                $validOptions = collect($this->getSubquestionTriggerOptionsForEdit($index))
                    ->pluck('value')
                    ->all();

                $condition = trim((string) ($question['condition_value'] ?? ''));
                if ($condition === '') {
                    $this->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_required'));
                } elseif (!in_array($condition, $validOptions, true)) {
                    $this->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_invalid'));
                } else {
                    $parentIndex = $this->getParentMainIndexFor($index);
                    if ($parentIndex !== null) {
                        $usedValues = $this->getUsedTriggerValuesForParent($parentIndex, $index);
                        if (in_array($condition, $usedValues, true)) {
                            $this->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_already_used'));
                        }
                    }
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
            $this->questions[$index]['is_subquestion'] = (bool) ($question['is_subquestion'] ?? false);
            $this->questions[$index]['parent_client_key'] = (string) ($question['parent_client_key'] ?? '');
            $this->questions[$index]['condition_value'] = trim((string) ($question['condition_value'] ?? ''));
            $this->questions[$index]['options'] = $options;
            $this->questions[$index]['answer_procedure_map'] = (array) ($question['answer_procedure_map'] ?? []);
            $this->questions[$index]['is_collapsed'] = $this->toBool($question['is_collapsed'] ?? false);
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

    private function syncQuestionDependencies(): void
    {
        foreach ($this->questions as $index => $question) {
            $isSub = (bool) ($question['is_subquestion'] ?? false);
            $parentIndex = $this->getParentMainIndexFor($index);

            if ($isSub && $parentIndex !== null) {
                $this->questions[$index]['parent_client_key'] = (string) ($this->questions[$parentIndex]['client_key'] ?? '');
                continue;
            }

            $this->questions[$index]['is_subquestion'] = false;
            $this->questions[$index]['parent_client_key'] = '';
            $this->questions[$index]['condition_value'] = '';
        }
    }

    public function getMainQuestionNumber(int $index): int
    {
        $number = 0;
        for ($i = 0; $i <= $index; $i++) {
            if (($this->questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }
            $number++;
        }

        return max(1, $number);
    }

    public function getSubquestionParentMainNumber(int $index): int
    {
        $parentIndex = $this->getParentMainIndexFor($index);
        if ($parentIndex === null) {
            return 0;
        }

        return $this->getMainQuestionNumber($parentIndex);
    }

    public function getSubquestionNumberInParent(int $index): int
    {
        $parentIndex = $this->getParentMainIndexFor($index);
        if ($parentIndex === null) {
            return 0;
        }

        $number = 0;
        for ($i = $parentIndex + 1; $i <= $index; $i++) {
            if (!($this->questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }
            if ($this->getParentMainIndexFor($i) !== $parentIndex) {
                continue;
            }
            $number++;
        }

        return max(1, $number);
    }

    public function getSubquestionTriggerLabel(int $index): string
    {
        $value = (string) ($this->questions[$index]['condition_value'] ?? '');
        if ($value === '') {
            return '';
        }

        $option = collect($this->getSubquestionTriggerOptionsForEdit($index))
            ->first(fn (array $item) => (string) ($item['value'] ?? '') === $value);

        return (string) ($option['label'] ?? '');
    }

    public function getNewSubquestionTriggerOptions(): array
    {
        $parentIndex = $this->getLastMainQuestionIndex();
        if ($parentIndex === null) {
            return [];
        }

        $usedValues = $this->getUsedTriggerValuesForParent($parentIndex);

        return collect($this->getTriggerOptionsForQuestion($parentIndex))
            ->filter(fn (array $option) => !in_array((string) ($option['value'] ?? ''), $usedValues, true))
            ->values()
            ->all();
    }

    public function getSubquestionTriggerOptionsForEdit(int $index): array
    {
        $parentIndex = $this->getParentMainIndexFor($index);
        if ($parentIndex === null) {
            return [];
        }

        $currentValue = trim((string) ($this->questions[$index]['condition_value'] ?? ''));
        $usedValues = $this->getUsedTriggerValuesForParent($parentIndex, $index);

        return collect($this->getTriggerOptionsForQuestion($parentIndex))
            ->filter(function (array $option) use ($usedValues, $currentValue) {
                $value = (string) ($option['value'] ?? '');
                if ($value === $currentValue) {
                    return true;
                }

                return !in_array($value, $usedValues, true);
            })
            ->values()
            ->all();
    }

    private function getUsedTriggerValuesForParent(int $parentIndex, ?int $ignoreQuestionIndex = null): array
    {
        $used = [];
        foreach ($this->questions as $index => $question) {
            if ($ignoreQuestionIndex !== null && $index === $ignoreQuestionIndex) {
                continue;
            }

            if (!($question['is_subquestion'] ?? false)) {
                continue;
            }

            if ($this->getParentMainIndexFor($index) !== $parentIndex) {
                continue;
            }

            $value = trim((string) ($question['condition_value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $used[] = $value;
        }

        return array_values(array_unique($used));
    }

    private function getTriggerOptionsForQuestion(int $questionIndex): array
    {
        if (!isset($this->questions[$questionIndex])) {
            return [];
        }

        $question = $this->questions[$questionIndex];
        $type = (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);

        if ($type === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
            return [
                ['value' => 'yes', 'label' => trans('service-order::messages.confirm_yes')],
                ['value' => 'no', 'label' => trans('service-order::messages.confirm_no')],
            ];
        }

        if ($type !== ServiceOrderAnalysisQuestion::TYPE_SELECT) {
            return [];
        }

        return collect((array) ($question['options'] ?? []))
            ->map(function (array $option) {
                $key = (string) ($option['key'] ?? '');
                $label = trim((string) ($option['label'] ?? ''));
                if ($key === '' || $label === '') {
                    return null;
                }

                return [
                    'value' => $key,
                    'label' => $label,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function getParentMainIndexFor(int $index): ?int
    {
        if (!isset($this->questions[$index])) {
            return null;
        }

        if (!($this->questions[$index]['is_subquestion'] ?? false)) {
            return null;
        }

        for ($i = $index - 1; $i >= 0; $i--) {
            if (($this->questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }
            return $i;
        }

        return null;
    }

    private function getLastMainQuestionIndex(): ?int
    {
        for ($i = count($this->questions) - 1; $i >= 0; $i--) {
            if (($this->questions[$i]['is_subquestion'] ?? false)) {
                continue;
            }
            return $i;
        }

        return null;
    }

    private function getSubquestionInsertIndexForParent(int $parentIndex): int
    {
        $insert = $parentIndex + 1;
        for ($i = $parentIndex + 1; $i < count($this->questions); $i++) {
            if (!($this->questions[$i]['is_subquestion'] ?? false)) {
                break;
            }
            if ($this->getParentMainIndexFor($i) !== $parentIndex) {
                break;
            }
            $insert = $i + 1;
        }

        return $insert;
    }

    private function getMainQuestionIndexes(): array
    {
        $indexes = [];
        foreach ($this->questions as $index => $question) {
            if (($question['is_subquestion'] ?? false)) {
                continue;
            }
            $indexes[] = $index;
        }

        return $indexes;
    }

    private function resolveMainStartIndex(int $index): ?int
    {
        if (!isset($this->questions[$index])) {
            return null;
        }

        if (!($this->questions[$index]['is_subquestion'] ?? false)) {
            return $index;
        }

        return $this->getParentMainIndexFor($index);
    }

    private function getMainBlockEndIndex(int $mainStart): ?int
    {
        if (!isset($this->questions[$mainStart]) || ($this->questions[$mainStart]['is_subquestion'] ?? false)) {
            return null;
        }

        $end = $mainStart;
        for ($i = $mainStart + 1; $i < count($this->questions); $i++) {
            if (!($this->questions[$i]['is_subquestion'] ?? false)) {
                break;
            }
            $end = $i;
        }

        return $end;
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

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'off', 'no', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
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
            'is_subquestion' => (bool) ($flags['is_subquestion'] ?? false),
            'parent_client_key' => (string) ($flags['parent_client_key'] ?? ''),
            'condition_value' => (string) ($flags['condition_value'] ?? ''),
            'is_required' => (bool) ($flags['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($flags['is_technical_description_required'] ?? false),
            'is_image_required' => (bool) ($flags['is_image_required'] ?? false),
            'required_images_count' => min(5, max(1, (int) ($flags['required_images_count'] ?? 1))),
            'has_help' => (bool) ($flags['has_help'] ?? false),
            'help_content' => '',
            'is_collapsed' => $this->toBool($flags['is_collapsed'] ?? false),
            'options' => $questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT ? [
                ['key' => (string) Str::uuid(), 'label' => '', 'procedure_id' => ''],
            ] : [],
            'answer_procedure_map' => [
                'yes' => ['procedure_id' => ''],
                'no' => ['procedure_id' => ''],
            ],
        ];
    }

    private function getAllowedProcedureIds(): array
    {
        return ServiceOrderProcedure::query()
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => true])
            ->all();
    }

    public function render()
    {
        return view('service-order::livewire.analysis.analysis-management');
    }
}
