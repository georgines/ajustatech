<?php

namespace Ajustatech\ServiceOrder\Services\EquipmentType\Contracts;

use Ajustatech\ServiceOrder\Livewire\EquipmentType\EquipmentTypeManagement;

interface EquipmentTypeManagementServiceInterface
{
    public function mount(EquipmentTypeManagement $component, ?string $id = null): void;

    public function createPdfDocumentFromModal(EquipmentTypeManagement $component): void;
    public function startEditPdfDocument(EquipmentTypeManagement $component, int $index): void;
    public function duplicatePdfDocument(EquipmentTypeManagement $component, int $index): void;
    public function movePdfDocumentUp(EquipmentTypeManagement $component, int $index): void;
    public function movePdfDocumentDown(EquipmentTypeManagement $component, int $index): void;

    public function createEditableDocumentFromModal(EquipmentTypeManagement $component): void;
    public function startEditEditableDocument(EquipmentTypeManagement $component, int $index): void;
    public function duplicateEditableDocument(EquipmentTypeManagement $component, int $index): void;
    public function moveEditableDocumentUp(EquipmentTypeManagement $component, int $index): void;
    public function moveEditableDocumentDown(EquipmentTypeManagement $component, int $index): void;

    public function createTextFieldFromModal(EquipmentTypeManagement $component): void;
    public function startEditTextField(EquipmentTypeManagement $component, int $index): void;
    public function duplicateTextField(EquipmentTypeManagement $component, int $index): void;
    public function moveTextFieldUp(EquipmentTypeManagement $component, int $index): void;
    public function moveTextFieldDown(EquipmentTypeManagement $component, int $index): void;

    public function createImageFieldFromModal(EquipmentTypeManagement $component): void;
    public function startEditImageField(EquipmentTypeManagement $component, int $index): void;
    public function duplicateImageField(EquipmentTypeManagement $component, int $index): void;
    public function moveImageFieldUp(EquipmentTypeManagement $component, int $index): void;
    public function moveImageFieldDown(EquipmentTypeManagement $component, int $index): void;

    public function removePdfDocument(EquipmentTypeManagement $component, int $index): void;

    public function removeEditableDocument(EquipmentTypeManagement $component, int $index): void;

    public function removeTextField(EquipmentTypeManagement $component, int $index): void;

    public function removeImageField(EquipmentTypeManagement $component, int $index): void;

    public function save(EquipmentTypeManagement $component): mixed;
}
