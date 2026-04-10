<?php

namespace Ajustatech\ServiceOrder\Livewire\EquipmentType;

use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeManagementServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class EquipmentTypeManagement extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $title = '';

    #[Locked]
    public string $mode = 'create';

    #[Locked]
    public ?string $equipmentTypeId = null;

    public string $name = '';

    public string $description = '';

    public bool $isActive = true;

    public array $pdfDocuments = [];

    public array $editableDocuments = [];

    public array $textFields = [];

    public array $imageFields = [];

    public array $initialPdfFiles = [];

    public array $initialImageFiles = [];

    public string $newPdfTitle = '';

    public string $newPdfDescription = '';

    public mixed $newPdfFile = null;
    public ?string $newPdfTemporaryUrl = null;
    public ?int $editingPdfIndex = null;

    public string $newEditableTitle = '';

    public string $newEditableDescription = '';

    public string $newEditableContent = '';

    public string $newEditableVariables = '';
    public ?int $editingEditableIndex = null;

    public string $newTextLabel = '';

    public string $newTextPlaceholder = '';

    public bool $newTextRequired = false;
    public ?int $editingTextIndex = null;

    public string $newImageLabel = '';

    public bool $newImageRequired = false;
    public ?int $editingImageIndex = null;

    protected EquipmentTypeManagementServiceInterface $managementServiceInstance;

    public function boot(EquipmentTypeManagementServiceInterface $managementService): void
    {
        $this->managementServiceInstance = $managementService;
    }

    protected function managementService(): EquipmentTypeManagementServiceInterface
    {
        return $this->managementServiceInstance;
    }

    public function mount(?string $id = null): void
    {
        $this->managementService()->mount($this, $id);
    }

    public function createPdfDocumentFromModal(): void
    {
        $this->managementService()->createPdfDocumentFromModal($this);
    }

    public function updatingNewPdfFile(): void
    {
        if ($this->newPdfFile instanceof TemporaryUploadedFile) {
            try {
                $this->newPdfFile->delete();
            } catch (\Throwable $exception) {
            }
        }

        $this->newPdfTemporaryUrl = null;
    }

    public function updatedNewPdfFile(): void
    {
        $this->newPdfTemporaryUrl = null;

        if (! $this->newPdfFile || ! method_exists($this->newPdfFile, 'temporaryUrl')) {
            return;
        }

        try {
            $this->newPdfTemporaryUrl = (string) $this->newPdfFile->temporaryUrl();
        } catch (\Throwable $exception) {
            $this->newPdfTemporaryUrl = null;
        }
    }
    public function startEditPdfDocument(int $index): void { $this->managementService()->startEditPdfDocument($this, $index); }
    public function duplicatePdfDocument(int $index): void { $this->managementService()->duplicatePdfDocument($this, $index); }
    public function movePdfDocumentUp(int $index): void { $this->managementService()->movePdfDocumentUp($this, $index); }
    public function movePdfDocumentDown(int $index): void { $this->managementService()->movePdfDocumentDown($this, $index); }

    public function createEditableDocumentFromModal(): void
    {
        $this->managementService()->createEditableDocumentFromModal($this);
    }
    public function startEditEditableDocument(int $index): void { $this->managementService()->startEditEditableDocument($this, $index); }
    public function duplicateEditableDocument(int $index): void { $this->managementService()->duplicateEditableDocument($this, $index); }
    public function moveEditableDocumentUp(int $index): void { $this->managementService()->moveEditableDocumentUp($this, $index); }
    public function moveEditableDocumentDown(int $index): void { $this->managementService()->moveEditableDocumentDown($this, $index); }

    public function createTextFieldFromModal(): void
    {
        $this->managementService()->createTextFieldFromModal($this);
    }
    public function startEditTextField(int $index): void { $this->managementService()->startEditTextField($this, $index); }
    public function duplicateTextField(int $index): void { $this->managementService()->duplicateTextField($this, $index); }
    public function moveTextFieldUp(int $index): void { $this->managementService()->moveTextFieldUp($this, $index); }
    public function moveTextFieldDown(int $index): void { $this->managementService()->moveTextFieldDown($this, $index); }

    public function createImageFieldFromModal(): void
    {
        $this->managementService()->createImageFieldFromModal($this);
    }
    public function startEditImageField(int $index): void { $this->managementService()->startEditImageField($this, $index); }
    public function duplicateImageField(int $index): void { $this->managementService()->duplicateImageField($this, $index); }
    public function moveImageFieldUp(int $index): void { $this->managementService()->moveImageFieldUp($this, $index); }
    public function moveImageFieldDown(int $index): void { $this->managementService()->moveImageFieldDown($this, $index); }

    public function removePdfDocument(int $index): void
    {
        $this->managementService()->removePdfDocument($this, $index);
    }

    public function removeEditableDocument(int $index): void
    {
        $this->managementService()->removeEditableDocument($this, $index);
    }

    public function removeTextField(int $index): void
    {
        $this->managementService()->removeTextField($this, $index);
    }

    public function removeImageField(int $index): void
    {
        $this->managementService()->removeImageField($this, $index);
    }

    public function save(): mixed
    {
        return $this->managementService()->save($this);
    }

    public function render()
    {
        return view('service-order::livewire.equipment-type.equipment-type-management');
    }
}
