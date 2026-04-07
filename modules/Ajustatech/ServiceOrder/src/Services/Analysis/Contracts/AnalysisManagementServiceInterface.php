<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;

interface AnalysisManagementServiceInterface
{
    public function mount(AnalysisManagement $component, ?string $id = null): void;

    public function resetNewQuestionDraft(AnalysisManagement $component): void;

    public function resetQuestionModalState(AnalysisManagement $component): void;

    public function resetQuestionHelpModalState(AnalysisManagement $component): void;

    public function addQuestion(AnalysisManagement $component): void;

    public function openCreateQuestionModal(AnalysisManagement $component, ?int $afterIndex = null): void;

    public function createQuestionFromModal(AnalysisManagement $component, string $type): void;

    public function removeQuestion(AnalysisManagement $component, int $index): void;

    public function confirmRemoveQuestion(AnalysisManagement $component, int $index): void;

    public function removeQuestionConfirmed(AnalysisManagement $component, int $index): void;

    public function editQuestion(AnalysisManagement $component, int $index): void;

    public function saveQuestionOptionsFromModal(AnalysisManagement $component): void;

    public function setQuestionTypeOnEdit(AnalysisManagement $component, string $type): void;

    public function openQuestionHelpModal(AnalysisManagement $component, int $index): void;

    public function saveQuestionHelpFromModal(AnalysisManagement $component): void;

    public function moveQuestionUp(AnalysisManagement $component, int $index): void;

    public function moveQuestionDown(AnalysisManagement $component, int $index): void;

    public function toggleQuestionCollapse(AnalysisManagement $component, string $clientKey): void;

    public function toggleQuestionCollapseByIndex(AnalysisManagement $component, int $index): void;

    public function canMoveQuestionUp(AnalysisManagement $component, int $index): bool;

    public function canMoveQuestionDown(AnalysisManagement $component, int $index): bool;

    public function addOption(AnalysisManagement $component, int $index): void;

    public function removeOption(AnalysisManagement $component, int $questionIndex, int $optionIndex): void;

    public function save(AnalysisManagement $component): mixed;

    public function getMainQuestionNumber(AnalysisManagement $component, int $index): int;

    public function getSubquestionParentMainNumber(AnalysisManagement $component, int $index): int;

    public function getSubquestionNumberInParent(AnalysisManagement $component, int $index): int;

    public function getSubquestionTriggerLabel(AnalysisManagement $component, int $index): string;

    public function getNewSubquestionTriggerOptions(AnalysisManagement $component): array;

    public function getSubquestionTriggerOptionsForEdit(AnalysisManagement $component, int $index): array;
}
