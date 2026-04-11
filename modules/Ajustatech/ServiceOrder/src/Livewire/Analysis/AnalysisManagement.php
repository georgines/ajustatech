<?php

namespace Ajustatech\ServiceOrder\Livewire\Analysis;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisManagementServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class AnalysisManagement extends Component
{
    use SwitchAlertDispatch;

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

    protected AnalysisManagementServiceInterface $managementServiceInstance;

    public function boot(AnalysisManagementServiceInterface $managementService): void
    {
        $this->managementServiceInstance = $managementService;
    }

    protected function managementService(): AnalysisManagementServiceInterface
    {
        return $this->managementServiceInstance;
    }

    public function mount(?string $id = null): void
    {
        $this->managementService()->mount($this, $id);
    }

    public function resetNewQuestionDraft(): void
    {
        $this->managementService()->resetNewQuestionDraft($this);
    }

    public function resetQuestionModalState(): void
    {
        $this->managementService()->resetQuestionModalState($this);
    }

    public function resetQuestionHelpModalState(): void
    {
        $this->managementService()->resetQuestionHelpModalState($this);
    }

    public function addQuestion(): void
    {
        $this->managementService()->addQuestion($this);
    }

    public function openCreateQuestionModal(?int $afterIndex = null): void
    {
        $this->managementService()->openCreateQuestionModal($this, $afterIndex);
    }

    public function createQuestionFromModal(string $type): void
    {
        $this->managementService()->createQuestionFromModal($this, $type);
    }

    public function removeQuestion(int $index): void
    {
        $this->managementService()->removeQuestion($this, $index);
    }

    public function confirmRemoveQuestion(int $index): void
    {
        $this->managementService()->confirmRemoveQuestion($this, $index);
    }

    #[On('analysis-remove-question')]
    public function removeQuestionConfirmed(int $index): void
    {
        $this->managementService()->removeQuestionConfirmed($this, $index);
    }

    public function editQuestion(int $index): void
    {
        $this->managementService()->editQuestion($this, $index);
    }

    public function saveQuestionOptionsFromModal(): void
    {
        $this->managementService()->saveQuestionOptionsFromModal($this);
    }

    public function setQuestionTypeOnEdit(string $type): void
    {
        $this->managementService()->setQuestionTypeOnEdit($this, $type);
    }

    public function openQuestionHelpModal(int $index): void
    {
        $this->managementService()->openQuestionHelpModal($this, $index);
    }

    public function saveQuestionHelpFromModal(): void
    {
        $this->managementService()->saveQuestionHelpFromModal($this);
    }

    public function moveQuestionUp(int $index): void
    {
        $this->managementService()->moveQuestionUp($this, $index);
    }

    public function moveQuestionDown(int $index): void
    {
        $this->managementService()->moveQuestionDown($this, $index);
    }

    public function toggleQuestionCollapse(string $clientKey): void
    {
        $this->managementService()->toggleQuestionCollapse($this, $clientKey);
    }

    public function toggleQuestionCollapseByIndex(int $index): void
    {
        $this->managementService()->toggleQuestionCollapseByIndex($this, $index);
    }

    public function canMoveQuestionUp(int $index): bool
    {
        return $this->managementService()->canMoveQuestionUp($this, $index);
    }

    public function canMoveQuestionDown(int $index): bool
    {
        return $this->managementService()->canMoveQuestionDown($this, $index);
    }

    public function addOption(int $index): void
    {
        $this->managementService()->addOption($this, $index);
    }

    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        $this->managementService()->removeOption($this, $questionIndex, $optionIndex);
    }

    public function save()
    {
        return $this->managementService()->save($this);
    }

    public function getMainQuestionNumber(int $index): int
    {
        return $this->managementService()->getMainQuestionNumber($this, $index);
    }

    public function getSubquestionParentMainNumber(int $index): int
    {
        return $this->managementService()->getSubquestionParentMainNumber($this, $index);
    }

    public function getSubquestionNumberInParent(int $index): int
    {
        return $this->managementService()->getSubquestionNumberInParent($this, $index);
    }

    public function getSubquestionTriggerLabel(int $index): string
    {
        return $this->managementService()->getSubquestionTriggerLabel($this, $index);
    }

    public function getNewSubquestionTriggerOptions(): array
    {
        return $this->managementService()->getNewSubquestionTriggerOptions($this);
    }

    public function getSubquestionTriggerOptionsForEdit(int $index): array
    {
        return $this->managementService()->getSubquestionTriggerOptionsForEdit($this, $index);
    }

    public function render()
    {
        return view('service-order::livewire.analysis.analysis-management');
    }
}
