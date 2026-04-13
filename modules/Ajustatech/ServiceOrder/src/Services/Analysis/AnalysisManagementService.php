<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisQuestion;
use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisManagementServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisServiceInterface;
use Illuminate\Support\Str;

class AnalysisManagementService implements AnalysisManagementServiceInterface
{
    public function __construct(
        protected AnalysisQuestionFormService $questionFormService,
        protected AnalysisQuestionWorkflowService $questionWorkflowService,
        protected AnalysisServiceInterface $analysisService,
    ) {}

    public function mount(AnalysisManagement $component, ?string $id = null): void
    {
        $component->availableProcedures = ServiceOrderAnalysisService::listProcedureOptions();
        $component->questions = [$this->questionFormService->newQuestionRow(1)];
        $this->resetNewQuestionDraft($component);
        $component->title = trans('service-order::messages.analysis_service_create_title');

        if (! $id) {
            return;
        }

        $analysis = $this->analysisService->findAnalysisService($id);

        $component->mode = 'edit';
        $component->analysisServiceId = $analysis->id;
        $component->name = (string) $analysis->name;
        $component->description = (string) ($analysis->description ?? '');
        $component->value = (float) $analysis->value;
        $component->title = trans('service-order::messages.analysis_service_edit_title');

        $mappedQuestions = $analysis->questions
            ->sortBy('sequence')
            ->values()
            ->map(fn ($question) => $this->questionFormService->fromPersistedQuestion($question))
            ->all();

        $component->questions = empty($mappedQuestions) ? [$this->questionFormService->newQuestionRow(1)] : $mappedQuestions;
        $component->questions = $this->questionWorkflowService->resequenceQuestions($component->questions);
        $component->questions = $this->questionWorkflowService->syncQuestionDependencies($component->questions);
    }

    public function resetNewQuestionDraft(AnalysisManagement $component): void
    {
        $component->newQuestionDraft = [
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

    public function resetQuestionModalState(AnalysisManagement $component): void
    {
        $component->editingQuestionIndex = null;
        $this->resetNewQuestionDraft($component);
    }

    public function resetQuestionHelpModalState(AnalysisManagement $component): void
    {
        $component->editingHelpQuestionIndex = null;
        $component->questionHelpDraft = ['content' => ''];
    }

    public function addQuestion(AnalysisManagement $component): void
    {
        $component->questions[] = $this->questionFormService->newQuestionRow(count($component->questions) + 1);
        $component->questions = $this->questionWorkflowService->resequenceQuestions($component->questions);
    }

    public function openCreateQuestionModal(AnalysisManagement $component, ?int $afterIndex = null): void
    {
        $component->resetValidation();
        $component->editingQuestionIndex = null;
        $this->resetNewQuestionDraft($component);

        $component->dispatch('analysis-question-create-open-modal');
    }

    public function createQuestionFromModal(AnalysisManagement $component, string $type): void
    {
        if ($component->editingQuestionIndex !== null) {
            return;
        }

        if (! in_array($type, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            return;
        }

        $component->resetValidation();
        $component->newQuestionDraft['question_type'] = $type;

        $isSubquestion = (bool) ($component->newQuestionDraft['is_subquestion'] ?? false);
        $conditionValue = trim((string) ($component->newQuestionDraft['condition_value'] ?? ''));
        $parentIndex = $isSubquestion ? $this->questionWorkflowService->getLastMainQuestionIndex($component->questions) : null;

        if ($isSubquestion) {
            if ($parentIndex === null) {
                $component->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_required'));

                return;
            }

            $triggerValues = collect($this->questionWorkflowService->getNewSubquestionTriggerOptions($component->questions))->pluck('value')->all();
            if ($conditionValue === '' || ! in_array($conditionValue, $triggerValues, true)) {
                $component->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_invalid'));

                return;
            }
        }

        $isImageRequired = (bool) ($component->newQuestionDraft['is_image_required'] ?? false);
        $requiredImagesCount = (int) ($component->newQuestionDraft['required_images_count'] ?? 1);
        $sequence = count($component->questions) + 1;
        $flags = [
            'is_subquestion' => $isSubquestion,
            'parent_client_key' => $parentIndex !== null ? (string) ($component->questions[$parentIndex]['client_key'] ?? '') : '',
            'condition_value' => $isSubquestion ? $conditionValue : '',
            'is_required' => (bool) ($component->newQuestionDraft['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($component->newQuestionDraft['is_technical_description_required'] ?? false),
            'is_image_required' => $isImageRequired,
            'required_images_count' => $isImageRequired ? min(5, max(1, $requiredImagesCount)) : 1,
            'has_help' => (bool) ($component->newQuestionDraft['has_help'] ?? false),
        ];

        $question = $this->questionFormService->newQuestionRow($sequence, $type, $flags);
        $insertIndex = $isSubquestion && $parentIndex !== null
            ? $this->getSubquestionInsertIndexForParent($component, $parentIndex)
            : count($component->questions);

        array_splice($component->questions, $insertIndex, 0, [$question]);
        $component->questions = $this->questionWorkflowService->resequenceQuestions($component->questions);
        $component->questions = $this->questionWorkflowService->syncQuestionDependencies($component->questions);

        $component->dispatch('analysis-question-added');
        $this->resetQuestionModalState($component);
    }

    public function removeQuestion(AnalysisManagement $component, int $index): void
    {
        $component->questions = $this->questionWorkflowService->removeQuestion($component->questions, $index);
        $component->questions = $this->questionWorkflowService->resequenceQuestions($component->questions);
        $component->questions = $this->questionWorkflowService->syncQuestionDependencies($component->questions);
    }

    public function confirmRemoveQuestion(AnalysisManagement $component, int $index): void
    {
        $message = trans('service-order::messages.analysis_confirm_delete_question_generic');

        $component->dispatchConfirmation($message)
            ->to('analysis-remove-question', index: $index)
            ->typeWarning()
            ->setButtonOK(trans('service-order::messages.confirm_yes'))
            ->setButtonCancel(trans('service-order::messages.confirm_no'))
            ->run();
    }

    public function removeQuestionConfirmed(AnalysisManagement $component, int $index): void
    {
        $this->removeQuestion($component, $index);
    }

    public function editQuestion(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index])) {
            return;
        }

        $question = $component->questions[$index];
        $component->resetValidation();
        $component->editingQuestionIndex = $index;
        $component->newQuestionDraft = [
            'question_type' => (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO),
            'is_subquestion' => (bool) ($question['is_subquestion'] ?? false),
            'condition_value' => (string) ($question['condition_value'] ?? ''),
            'is_required' => (bool) ($question['is_required'] ?? false),
            'is_technical_description_required' => (bool) ($question['is_technical_description_required'] ?? false),
            'is_image_required' => (bool) ($question['is_image_required'] ?? false),
            'required_images_count' => min(5, max(1, (int) ($question['required_images_count'] ?? 1))),
            'has_help' => (bool) ($question['has_help'] ?? false),
        ];

        $component->dispatch('analysis-question-edit-open-modal');
    }

    public function saveQuestionOptionsFromModal(AnalysisManagement $component): void
    {
        if ($component->editingQuestionIndex === null || ! isset($component->questions[$component->editingQuestionIndex])) {
            return;
        }

        $isImageRequired = (bool) ($component->newQuestionDraft['is_image_required'] ?? false);
        $requiredImagesCount = (int) ($component->newQuestionDraft['required_images_count'] ?? 1);
        $selectedType = (string) ($component->newQuestionDraft['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
        if (! in_array($selectedType, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            $selectedType = ServiceOrderAnalysisQuestion::TYPE_YES_NO;
        }
        $isSubquestion = (bool) ($component->newQuestionDraft['is_subquestion'] ?? false);
        $conditionValue = trim((string) ($component->newQuestionDraft['condition_value'] ?? ''));
        $triggerValues = collect($this->questionWorkflowService->getSubquestionTriggerOptionsForEdit($component->questions, $component->editingQuestionIndex))->pluck('value')->all();

        $component->questions[$component->editingQuestionIndex]['question_type'] = $selectedType;
        $component->questions[$component->editingQuestionIndex]['is_subquestion'] = $isSubquestion;
        $component->questions[$component->editingQuestionIndex]['condition_value'] = $isSubquestion ? $conditionValue : '';
        $component->questions[$component->editingQuestionIndex]['is_required'] = (bool) ($component->newQuestionDraft['is_required'] ?? false);
        $component->questions[$component->editingQuestionIndex]['is_technical_description_required'] = (bool) ($component->newQuestionDraft['is_technical_description_required'] ?? false);
        $component->questions[$component->editingQuestionIndex]['is_image_required'] = $isImageRequired;
        $component->questions[$component->editingQuestionIndex]['required_images_count'] = $isImageRequired
            ? min(5, max(1, $requiredImagesCount))
            : 1;
        $component->questions[$component->editingQuestionIndex]['has_help'] = (bool) ($component->newQuestionDraft['has_help'] ?? false);

        if (! ($component->questions[$component->editingQuestionIndex]['has_help'] ?? false)) {
            $component->questions[$component->editingQuestionIndex]['help_content'] = '';
        }

        if ($isSubquestion && ($conditionValue === '' || ! in_array($conditionValue, $triggerValues, true))) {
            $component->addError('newQuestionDraft.condition_value', trans('service-order::messages.analysis_question_condition_invalid'));

            return;
        }

        if ($selectedType === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
            $component->questions[$component->editingQuestionIndex]['options'] = [];
            $component->questions[$component->editingQuestionIndex]['answer_procedure_map'] = [
                'yes' => ['procedure_id' => (string) (($component->questions[$component->editingQuestionIndex]['answer_procedure_map']['yes']['procedure_id'] ?? ''))],
                'no' => ['procedure_id' => (string) (($component->questions[$component->editingQuestionIndex]['answer_procedure_map']['no']['procedure_id'] ?? ''))],
            ];
        } else {
            $existingOptions = array_values((array) ($component->questions[$component->editingQuestionIndex]['options'] ?? []));
            if (empty($existingOptions)) {
                $existingOptions = [
                    ['key' => (string) Str::uuid(), 'label' => '', 'procedure_id' => ''],
                ];
            }
            $component->questions[$component->editingQuestionIndex]['options'] = $existingOptions;

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
            $component->questions[$component->editingQuestionIndex]['answer_procedure_map'] = $normalizedMap;
        }

        $component->questions = $this->questionWorkflowService->syncQuestionDependencies($component->questions);
        $component->dispatch('analysis-question-options-saved');
        $this->resetQuestionModalState($component);
    }

    public function setQuestionTypeOnEdit(AnalysisManagement $component, string $type): void
    {
        if ($component->editingQuestionIndex === null) {
            return;
        }

        if (! in_array($type, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
            return;
        }

        $component->newQuestionDraft['question_type'] = $type;
    }

    public function openQuestionHelpModal(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index]) || ! ($component->questions[$index]['has_help'] ?? false)) {
            return;
        }

        $component->editingHelpQuestionIndex = $index;
        $component->questionHelpDraft = [
            'content' => (string) ($component->questions[$index]['help_content'] ?? ''),
        ];

        $component->dispatch('analysis-question-help-open-modal');
    }

    public function saveQuestionHelpFromModal(AnalysisManagement $component): void
    {
        if ($component->editingHelpQuestionIndex === null || ! isset($component->questions[$component->editingHelpQuestionIndex])) {
            return;
        }

        $content = $this->questionFormService->sanitizeText((string) ($component->questionHelpDraft['content'] ?? ''), 2000);
        $component->questions[$component->editingHelpQuestionIndex]['help_content'] = $content;

        $component->dispatch('analysis-question-help-saved');
        $this->resetQuestionHelpModalState($component);
    }

    public function moveQuestionUp(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index])) {
            return;
        }

        $component->questions = $this->questionWorkflowService->moveQuestionUp($component->questions, $index);
    }

    public function moveQuestionDown(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index])) {
            return;
        }

        $component->questions = $this->questionWorkflowService->moveQuestionDown($component->questions, $index);
    }

    public function toggleQuestionCollapse(AnalysisManagement $component, string $clientKey): void
    {
        $normalizedKey = trim($clientKey);
        if ($normalizedKey === '') {
            return;
        }

        foreach ($component->questions as $index => $question) {
            if ((string) ($question['client_key'] ?? '') === $normalizedKey) {
                $this->toggleQuestionCollapseByIndex($component, $index);

                return;
            }
        }
    }

    public function toggleQuestionCollapseByIndex(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index])) {
            return;
        }

        $newCollapsedState = ! (bool) ($component->questions[$index]['is_collapsed'] ?? false);
        $component->questions[$index]['is_collapsed'] = $newCollapsedState;
    }

    public function canMoveQuestionUp(AnalysisManagement $component, int $index): bool
    {
        return $this->questionWorkflowService->canMoveQuestionUp($component->questions, $index);
    }

    public function canMoveQuestionDown(AnalysisManagement $component, int $index): bool
    {
        return $this->questionWorkflowService->canMoveQuestionDown($component->questions, $index);
    }

    public function addOption(AnalysisManagement $component, int $index): void
    {
        if (! isset($component->questions[$index])) {
            return;
        }

        if (count((array) ($component->questions[$index]['options'] ?? [])) >= 8) {
            return;
        }

        $component->questions[$index]['options'][] = [
            'key' => (string) Str::uuid(),
            'label' => '',
            'procedure_id' => '',
        ];
    }

    public function removeOption(AnalysisManagement $component, int $questionIndex, int $optionIndex): void
    {
        if (! isset($component->questions[$questionIndex]['options'][$optionIndex])) {
            return;
        }

        $removedOptionKey = (string) ($component->questions[$questionIndex]['options'][$optionIndex]['key'] ?? '');
        unset($component->questions[$questionIndex]['options'][$optionIndex]);
        $component->questions[$questionIndex]['options'] = array_values($component->questions[$questionIndex]['options']);

        if ($removedOptionKey !== '') {
            unset($component->questions[$questionIndex]['answer_procedure_map'][$removedOptionKey]);
        }
    }

    public function save(AnalysisManagement $component): mixed
    {
        $component->resetValidation();

        $sanitized = $this->questionFormService->sanitizeAnalysisForm(
            $component->name,
            $component->description,
            $component->value,
            $component->questions,
        );

        $component->name = $sanitized['name'];
        $component->description = $sanitized['description'];
        $component->value = $sanitized['value'];

        $this->sanitizeInputs($component);
        $this->resequenceQuestions($component);
        $this->syncQuestionDependencies($component);

        if (! $this->validateQuestionsStructure($component)) {
            return null;
        }

        $payload = [
            'name' => $component->name,
            'description' => $this->questionFormService->nullableValue($component->description),
            'value' => round((float) $component->value, 2),
        ];

        $questionPayload = $this->questionFormService->buildQuestionPayload($component->questions);

        if ($component->mode === 'edit' && $component->analysisServiceId) {
            $this->analysisService->updateAnalysisService($component->analysisServiceId, $payload, $questionPayload);
        } else {
            $this->analysisService->createAnalysisService($payload, $questionPayload);
        }

        return redirect()->route('service-order-analyses-show');
    }

    public function getMainQuestionNumber(AnalysisManagement $component, int $index): int
    {
        return $this->questionWorkflowService->getMainQuestionNumber($component->questions, $index);
    }

    public function getSubquestionParentMainNumber(AnalysisManagement $component, int $index): int
    {
        return $this->questionWorkflowService->getSubquestionParentMainNumber($component->questions, $index);
    }

    public function getSubquestionNumberInParent(AnalysisManagement $component, int $index): int
    {
        return $this->questionWorkflowService->getSubquestionNumberInParent($component->questions, $index);
    }

    public function getSubquestionTriggerLabel(AnalysisManagement $component, int $index): string
    {
        return $this->questionWorkflowService->getSubquestionTriggerLabel($component->questions, $index);
    }

    public function getNewSubquestionTriggerOptions(AnalysisManagement $component): array
    {
        return $this->questionWorkflowService->getNewSubquestionTriggerOptions($component->questions);
    }

    public function getSubquestionTriggerOptionsForEdit(AnalysisManagement $component, int $index): array
    {
        return $this->questionWorkflowService->getSubquestionTriggerOptionsForEdit($component->questions, $index);
    }

    private function validateQuestionsStructure(AnalysisManagement $component): bool
    {
        $allowedProcedureIds = $this->getAllowedProcedureIds($component);

        foreach ($component->questions as $index => $question) {
            $basePath = "questions.{$index}";
            $questionType = $question['question_type'] ?? '';
            $options = array_values(array_filter(
                (array) ($question['options'] ?? []),
                fn ($option) => trim((string) ($option['label'] ?? '')) !== ''
            ));

            if (trim((string) ($question['section_name'] ?? '')) === '') {
                $component->addError("{$basePath}.section_name", trans('validation.required', [
                    'attribute' => trans('service-order::messages.analysis_section_name'),
                ]));
            }

            if (trim((string) ($question['question_text'] ?? '')) === '') {
                $component->addError("{$basePath}.question_text", trans('validation.required', [
                    'attribute' => trans('service-order::messages.analysis_question_text'),
                ]));
            }

            if (! in_array($questionType, ServiceOrderAnalysisQuestion::allowedTypes(), true)) {
                $component->addError("{$basePath}.question_type", trans('validation.in', [
                    'attribute' => trans('service-order::messages.analysis_question_type'),
                ]));
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT && empty($options)) {
                $component->addError("{$basePath}.options", trans('service-order::messages.analysis_question_options_required'));
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT && count($options) > 8) {
                $component->addError("{$basePath}.options", trans('service-order::messages.analysis_question_options_max'));
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_YES_NO) {
                foreach (['yes', 'no'] as $answerKey) {
                    $procedureId = trim((string) data_get($question, "answer_procedure_map.{$answerKey}.procedure_id", ''));
                    if ($procedureId !== '' && ! isset($allowedProcedureIds[$procedureId])) {
                        $component->addError("{$basePath}.answer_procedure_map", trans('validation.exists', [
                            'attribute' => trans('service-order::messages.analysis_answer_procedures'),
                        ]));
                        break;
                    }
                }
            }

            if ($questionType === ServiceOrderAnalysisQuestion::TYPE_SELECT) {
                foreach ((array) ($question['options'] ?? []) as $option) {
                    $procedureId = trim((string) ($option['procedure_id'] ?? ''));
                    if ($procedureId !== '' && ! isset($allowedProcedureIds[$procedureId])) {
                        $component->addError("{$basePath}.answer_procedure_map", trans('validation.exists', [
                            'attribute' => trans('service-order::messages.analysis_answer_procedures'),
                        ]));
                        break;
                    }
                }
            }

            if ($question['is_image_required'] ?? false) {
                $requiredCount = (int) ($question['required_images_count'] ?? 0);
                if ($requiredCount < 1 || $requiredCount > 5) {
                    $component->addError("{$basePath}.required_images_count", trans('service-order::messages.analysis_required_images_count_invalid'));
                }
            }

            if ($question['is_subquestion'] ?? false) {
                if ($index === 0) {
                    $component->addError("{$basePath}.is_subquestion", trans('service-order::messages.analysis_subquestion_first_invalid'));
                }

                $validOptions = collect($this->questionWorkflowService->getSubquestionTriggerOptionsForEdit($component->questions, $index))
                    ->pluck('value')
                    ->all();

                $condition = trim((string) ($question['condition_value'] ?? ''));
                if ($condition === '') {
                    $component->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_required'));
                } elseif (! in_array($condition, $validOptions, true)) {
                    $component->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_invalid'));
                } else {
                    $parentIndex = $this->questionWorkflowService->getParentMainIndexFor($component->questions, $index);
                    if ($parentIndex !== null) {
                        $usedValues = $this->questionWorkflowService->getUsedTriggerValuesForParent($component->questions, $parentIndex, $index);
                        if (in_array($condition, $usedValues, true)) {
                            $component->addError("{$basePath}.condition_value", trans('service-order::messages.analysis_question_condition_already_used'));
                        }
                    }
                }
            }
        }

        return ! $component->getErrorBag()->isNotEmpty();
    }

    private function sanitizeInputs(AnalysisManagement $component): void
    {
        $component->name = $this->questionFormService->sanitizeText($component->name, 255);
        $component->description = $this->questionFormService->sanitizeText($component->description, 1000);

        foreach ($component->questions as $index => $question) {
            $type = (string) ($question['question_type'] ?? ServiceOrderAnalysisQuestion::TYPE_YES_NO);
            $options = collect((array) ($question['options'] ?? []))
                ->map(fn ($option) => [
                    'key' => (string) ($option['key'] ?? Str::uuid()),
                    'label' => $this->questionFormService->sanitizeText((string) ($option['label'] ?? ''), 120),
                    'procedure_id' => (string) ($option['procedure_id'] ?? ''),
                ])
                ->filter(fn (array $option) => $option['label'] !== '')
                ->values()
                ->all();

            $component->questions[$index]['sequence'] = max(1, (int) ($question['sequence'] ?? ($index + 1)));
            $component->questions[$index]['section_name'] = $this->questionFormService->sanitizeText((string) ($question['section_name'] ?? 'Geral'), 120);
            $component->questions[$index]['question_text'] = $this->questionFormService->sanitizeText((string) ($question['question_text'] ?? ''), 500);
            $component->questions[$index]['question_type'] = $type;
            $component->questions[$index]['is_required'] = (bool) ($question['is_required'] ?? false);
            $component->questions[$index]['is_technical_description_required'] = (bool) ($question['is_technical_description_required'] ?? false);
            $component->questions[$index]['is_image_required'] = (bool) ($question['is_image_required'] ?? false);
            $component->questions[$index]['required_images_count'] = (int) ($question['required_images_count'] ?? 1);
            $component->questions[$index]['has_help'] = (bool) ($question['has_help'] ?? false);
            $component->questions[$index]['help_content'] = $this->questionFormService->sanitizeText((string) ($question['help_content'] ?? ''), 2000);
            $component->questions[$index]['is_subquestion'] = (bool) ($question['is_subquestion'] ?? false);
            $component->questions[$index]['parent_client_key'] = (string) ($question['parent_client_key'] ?? '');
            $component->questions[$index]['condition_value'] = trim((string) ($question['condition_value'] ?? ''));
            $component->questions[$index]['options'] = $options;
            $component->questions[$index]['answer_procedure_map'] = (array) ($question['answer_procedure_map'] ?? []);
            $component->questions[$index]['is_collapsed'] = $this->toBool($question['is_collapsed'] ?? false);
        }
    }

    private function resequenceQuestions(AnalysisManagement $component): void
    {
        $component->questions = $this->questionWorkflowService->resequenceQuestions($component->questions);
    }

    private function syncQuestionDependencies(AnalysisManagement $component): void
    {
        $component->questions = $this->questionWorkflowService->syncQuestionDependencies($component->questions);
    }

    private function getAllowedProcedureIds(AnalysisManagement $component): array
    {
        if (! empty($component->availableProcedures)) {
            return collect($component->availableProcedures)
                ->pluck('id')
                ->mapWithKeys(fn ($id) => [(string) $id => true])
                ->all();
        }

        return ServiceOrderProcedure::idMap();
    }

    private function getSubquestionInsertIndexForParent(AnalysisManagement $component, int $parentIndex): int
    {
        $insert = $parentIndex + 1;
        for ($i = $parentIndex + 1; $i < count($component->questions); $i++) {
            if (! ($component->questions[$i]['is_subquestion'] ?? false)) {
                break;
            }
            if ($this->questionWorkflowService->getParentMainIndexFor($component->questions, $i) !== $parentIndex) {
                break;
            }
            $insert = $i + 1;
        }

        return $insert;
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
}
