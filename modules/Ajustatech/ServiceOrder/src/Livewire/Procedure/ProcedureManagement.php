<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureManagementServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class ProcedureManagement extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $title = '';

    #[Locked]
    public string $mode = 'create';

    #[Locked]
    public ?string $procedureId = null;

    public string $name = '';

    public string $description = '';

    public mixed $value = null;

    public bool $hasHelp = false;

    public string $helpText = '';

    public array $videoItems = [];

    public array $imageItems = [];

    public array $pdfItems = [];

    public array $existingMedia = [];

    public array $deleteMediaIds = [];

    public string $newMediaType = 'image';

    public string $newMediaName = '';

    public string $newMediaDescription = '';

    public string $newMediaUrl = '';

    public mixed $newMediaFile = null;

    protected ProcedureManagementServiceInterface $managementServiceInstance;

    public function boot(ProcedureManagementServiceInterface $managementService): void
    {
        $this->managementServiceInstance = $managementService;
    }

    protected function managementService(): ProcedureManagementServiceInterface
    {
        return $this->managementServiceInstance;
    }

    public function mount(?string $id = null): void
    {
        $this->managementService()->mount($this, $id);
    }

    public function save(): mixed
    {
        return $this->managementService()->save($this);
    }

    public function updatedHasHelp(bool $value): void
    {
        $this->managementService()->updatedHasHelp($this, $value);
    }

    public function addMediaItem(): void
    {
        $this->managementService()->addMediaItem($this);
    }

    public function resetAddMediaForm(): void
    {
        $this->managementService()->resetAddMediaForm($this);
    }

    public function addVideoItem(): void
    {
        $this->managementService()->addVideoItem($this);
    }

    public function removeVideoItem(int $index): void
    {
        $this->managementService()->removeVideoItem($this, $index);
    }

    public function addImageItem(): void
    {
        $this->managementService()->addImageItem($this);
    }

    public function removeImageItem(int $index): void
    {
        $this->managementService()->removeImageItem($this, $index);
    }

    public function addPdfItem(): void
    {
        $this->managementService()->addPdfItem($this);
    }

    public function removePdfItem(int $index): void
    {
        $this->managementService()->removePdfItem($this, $index);
    }

    public function removeExistingMedia(string $mediaId): void
    {
        $this->managementService()->removeExistingMedia($this, $mediaId);
    }

    public function render()
    {
        return view('service-order::livewire.procedure.procedure-management');
    }
}
