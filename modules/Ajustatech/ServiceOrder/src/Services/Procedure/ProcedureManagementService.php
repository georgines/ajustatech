<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureFormServiceInterface;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureManagementServiceInterface;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureMediaServiceInterface;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;

class ProcedureManagementService implements ProcedureManagementServiceInterface
{
    public function __construct(
        protected ProcedureServiceInterface $procedureService,
        protected ProcedureFormServiceInterface $formService,
        protected ProcedureMediaServiceInterface $mediaService,
    ) {}

    public function mount(ProcedureManagement $component, ?string $id = null): void
    {
        $this->formService->initializeCreateState($component);
        $this->mediaService->initializeCreateState($component);

        if (! $id) {
            return;
        }

        $procedure = $this->procedureService->findProcedure($id);

        $component->mode = 'edit';
        $component->procedureId = $procedure->id;
        $this->formService->fillFromProcedure($component, $procedure);
        $this->mediaService->fillFromProcedure($component, $procedure);
    }

    public function save(ProcedureManagement $component): mixed
    {
        $this->formService->sanitizeInputs($component);

        $component->validate(
            $this->formService->validationRules($component),
            [],
            $this->formService->validationAttributes()
        );

        if ($component->hasHelp && ! $this->mediaService->hasHelpContent($component)) {
            $component->addError('hasHelp', trans('service-order::messages.procedure_help_required'));

            return null;
        }

        $payload = $this->formService->buildPayload($component);
        $media = $this->mediaService->buildMediaPayload($component);

        if ($component->mode === 'edit' && $component->procedureId) {
            $this->procedureService->updateProcedure($component->procedureId, $payload, $media, $component->deleteMediaIds);
        } else {
            $this->procedureService->createProcedure($payload, $media);
        }

        return redirect()->route('service-order-procedures-show');
    }

    public function updatedHasHelp(ProcedureManagement $component, bool $value): void
    {
        $this->mediaService->updatedHasHelp($component, $value);
    }

    public function addMediaItem(ProcedureManagement $component): void
    {
        $this->mediaService->addMediaItem($component);
    }

    public function resetAddMediaForm(ProcedureManagement $component): void
    {
        $this->mediaService->resetAddMediaForm($component);
    }

    public function addVideoItem(ProcedureManagement $component): void
    {
        $this->mediaService->addVideoItem($component);
    }

    public function removeVideoItem(ProcedureManagement $component, int $index): void
    {
        $this->mediaService->removeVideoItem($component, $index);
    }

    public function addImageItem(ProcedureManagement $component): void
    {
        $this->mediaService->addImageItem($component);
    }

    public function removeImageItem(ProcedureManagement $component, int $index): void
    {
        $this->mediaService->removeImageItem($component, $index);
    }

    public function addPdfItem(ProcedureManagement $component): void
    {
        $this->mediaService->addPdfItem($component);
    }

    public function removePdfItem(ProcedureManagement $component, int $index): void
    {
        $this->mediaService->removePdfItem($component, $index);
    }

    public function removeExistingMedia(ProcedureManagement $component, string $mediaId): void
    {
        $this->mediaService->removeExistingMedia($component, $mediaId);
    }
}
