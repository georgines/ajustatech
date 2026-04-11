<?php

namespace Ajustatech\ServiceOrder\Services\Procedure\Contracts;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;

interface ProcedureMediaServiceInterface
{
    public function initializeCreateState(ProcedureManagement $component): void;

    public function fillFromProcedure(ProcedureManagement $component, ServiceOrderProcedure $procedure): void;

    public function updatedHasHelp(ProcedureManagement $component, bool $value): void;

    public function addMediaItem(ProcedureManagement $component): void;

    public function resetAddMediaForm(ProcedureManagement $component): void;

    public function addVideoItem(ProcedureManagement $component): void;

    public function removeVideoItem(ProcedureManagement $component, int $index): void;

    public function addImageItem(ProcedureManagement $component): void;

    public function removeImageItem(ProcedureManagement $component, int $index): void;

    public function addPdfItem(ProcedureManagement $component): void;

    public function removePdfItem(ProcedureManagement $component, int $index): void;

    public function removeExistingMedia(ProcedureManagement $component, string $mediaId): void;

    public function buildMediaPayload(ProcedureManagement $component): array;

    public function hasHelpContent(ProcedureManagement $component): bool;
}
