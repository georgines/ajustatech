<?php

namespace Ajustatech\ServiceOrder\Services\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\EquipmentTypeManagement;
use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeManagementServiceInterface;
use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EquipmentTypeManagementService implements EquipmentTypeManagementServiceInterface
{
    public function __construct(protected EquipmentTypeServiceInterface $equipmentTypeService) {}

    public function mount(EquipmentTypeManagement $component, ?string $id = null): void
    {
        $this->initializeCreateState($component);

        if (! $id) {
            return;
        }

        $equipmentType = $this->equipmentTypeService->findEquipmentType($id);

        $component->mode = 'edit';
        $component->equipmentTypeId = $equipmentType->id;
        $component->title = trans('service-order::messages.equipment_type_edit_title');
        $component->name = $equipmentType->name;
        $component->description = (string) ($equipmentType->description ?? '');
        $component->isActive = (bool) $equipmentType->is_active;

        $component->pdfDocuments = $equipmentType->documents
            ->where('document_type', ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF)
            ->map(fn (ServiceOrderEquipmentTypeDocument $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => (string) ($document->description ?? ''),
                'file' => null,
                'temporary_preview_url' => null,
                'existing_disk' => $document->disk,
                'existing_path' => $document->path,
                'existing_original_name' => $document->original_name,
                'existing_mime_type' => $document->mime_type,
                'existing_extension' => $document->extension,
                'existing_size' => $document->size,
                'template_content' => null,
                'variables' => '',
            ])
            ->values()
            ->all();

        $component->editableDocuments = $equipmentType->documents
            ->where('document_type', ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE)
            ->map(fn (ServiceOrderEquipmentTypeDocument $document) => [
                'title' => $document->title,
                'description' => (string) ($document->description ?? ''),
                'content' => (string) ($document->template_content ?? ''),
                'variables' => implode(', ', (array) $document->variables_json),
            ])
            ->values()
            ->all();

        $component->textFields = $equipmentType->fields
            ->where('field_type', ServiceOrderEquipmentTypeField::TYPE_TEXT)
            ->map(fn (ServiceOrderEquipmentTypeField $field) => [
                'label' => $field->label,
                'placeholder' => (string) ($field->placeholder ?? ''),
                'is_required' => (bool) $field->is_required,
            ])
            ->values()
            ->all();

        $component->imageFields = $equipmentType->fields
            ->where('field_type', ServiceOrderEquipmentTypeField::TYPE_IMAGE)
            ->map(fn (ServiceOrderEquipmentTypeField $field) => [
                'id' => $field->id,
                'label' => $field->label,
                'is_required' => (bool) $field->is_required,
                'file' => null,
                'existing_disk' => $field->disk,
                'existing_path' => $field->path,
                'existing_original_name' => $field->original_name,
                'existing_mime_type' => $field->mime_type,
                'existing_extension' => $field->extension,
                'existing_size' => $field->size,
            ])
            ->values()
            ->all();

        $component->initialPdfFiles = $this->collectInitialFiles($component->pdfDocuments);
        $component->initialImageFiles = $this->collectInitialFiles($component->imageFields);
    }

    public function createPdfDocumentFromModal(EquipmentTypeManagement $component): void
    {
        $component->newPdfTitle = $this->sanitizeText($component->newPdfTitle, 150);
        $component->newPdfDescription = $this->sanitizeText($component->newPdfDescription, 1000);

        $rules = [
            'newPdfTitle' => 'required|string|max:150',
            'newPdfDescription' => 'nullable|string|max:1000',
        ];
        $existingHasFile = $component->editingPdfIndex !== null
            && isset($component->pdfDocuments[$component->editingPdfIndex])
            && $this->hasExistingFile($component->pdfDocuments[$component->editingPdfIndex]);
        $rules['newPdfFile'] = $existingHasFile ? 'nullable|file|mimes:pdf|max:10240' : 'required|file|mimes:pdf|max:10240';

        $component->validate($rules, [], [
            'newPdfTitle' => trans('service-order::messages.equipment_type_document_title'),
            'newPdfDescription' => trans('service-order::messages.equipment_type_document_description'),
            'newPdfFile' => trans('service-order::messages.equipment_type_pdf_file'),
        ]);

        $item = [
            'id' => null,
            'title' => $component->newPdfTitle,
            'description' => $component->newPdfDescription,
            'file' => $component->newPdfFile,
            'temporary_preview_url' => $component->newPdfTemporaryUrl,
            'existing_disk' => null,
            'existing_path' => null,
            'existing_original_name' => null,
            'existing_mime_type' => null,
            'existing_extension' => null,
            'existing_size' => null,
            'template_content' => null,
            'variables' => '',
        ];

        if ($component->editingPdfIndex !== null && isset($component->pdfDocuments[$component->editingPdfIndex])) {
            $existing = $component->pdfDocuments[$component->editingPdfIndex];
            $existingTempFile = $existing['file'] ?? null;
            if ($existingTempFile instanceof TemporaryUploadedFile
                && $existingTempFile !== $component->newPdfFile
                && ! $this->isTemporaryFileUsedElsewhere($component->pdfDocuments, $component->editingPdfIndex, $existingTempFile)) {
                try {
                    $existingTempFile->delete();
                } catch (\Throwable $exception) {
                }
            }

            $component->pdfDocuments[$component->editingPdfIndex] = array_merge($item, [
                'id' => $existing['id'] ?? null,
                'existing_disk' => $existing['existing_disk'] ?? null,
                'existing_path' => $existing['existing_path'] ?? null,
                'existing_original_name' => $existing['existing_original_name'] ?? null,
                'existing_mime_type' => $existing['existing_mime_type'] ?? null,
                'existing_extension' => $existing['existing_extension'] ?? null,
                'existing_size' => $existing['existing_size'] ?? null,
            ]);
        } else {
            $component->pdfDocuments[] = $item;
        }

        $this->resetPdfModalState($component);
        $component->dispatch('equipment-type-modal-close', modalId: 'addPdfDocumentModal');
    }

    public function startEditPdfDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->pdfDocuments[$index])) {
            return;
        }

        $item = $component->pdfDocuments[$index];
        $component->editingPdfIndex = $index;
        $component->newPdfTitle = (string) ($item['title'] ?? '');
        $component->newPdfDescription = (string) ($item['description'] ?? '');
        $component->newPdfFile = null;
        $component->newPdfTemporaryUrl = (string) ($item['temporary_preview_url'] ?? '');
        $component->dispatch('equipment-type-modal-open', modalId: 'addPdfDocumentModal');
    }

    public function duplicatePdfDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->pdfDocuments[$index])) {
            return;
        }

        $copy = $component->pdfDocuments[$index];
        $copy['id'] = null;
        $copy['title'] = trim(((string) ($copy['title'] ?? '')) . ' (Copia)');

        if ($this->hasExistingFile($copy)) {
            $duplicatedFile = $this->duplicateStoredFile(
                disk: (string) $copy['existing_disk'],
                path: (string) $copy['existing_path'],
                targetDirectory: 'service-order/equipment-types/documents/pdfs'
            );

            if (! empty($duplicatedFile)) {
                $copy['existing_disk'] = $duplicatedFile['disk'];
                $copy['existing_path'] = $duplicatedFile['path'];
            }
        }

        $component->pdfDocuments[] = $copy;
    }

    public function movePdfDocumentUp(EquipmentTypeManagement $component, int $index): void
    {
        $component->pdfDocuments = $this->moveItemUp($component->pdfDocuments, $index);
    }

    public function movePdfDocumentDown(EquipmentTypeManagement $component, int $index): void
    {
        $component->pdfDocuments = $this->moveItemDown($component->pdfDocuments, $index);
    }

    public function createEditableDocumentFromModal(EquipmentTypeManagement $component): void
    {
        $component->newEditableTitle = $this->sanitizeText($component->newEditableTitle, 150);
        $component->newEditableDescription = $this->sanitizeText($component->newEditableDescription, 1000);
        $component->newEditableContent = $this->sanitizeText($component->newEditableContent, 10000);
        $component->newEditableVariables = $this->sanitizeText($component->newEditableVariables, 1000);

        $component->validate([
            'newEditableTitle' => 'required|string|max:150',
            'newEditableDescription' => 'nullable|string|max:1000',
            'newEditableContent' => 'required|string|max:10000',
            'newEditableVariables' => 'nullable|string|max:1000',
        ], [], [
            'newEditableTitle' => trans('service-order::messages.equipment_type_document_title'),
            'newEditableDescription' => trans('service-order::messages.equipment_type_document_description'),
            'newEditableContent' => trans('service-order::messages.equipment_type_template_content'),
            'newEditableVariables' => trans('service-order::messages.equipment_type_variables_hint'),
        ]);

        $item = [
            'title' => $component->newEditableTitle,
            'description' => $component->newEditableDescription,
            'content' => $component->newEditableContent,
            'variables' => $component->newEditableVariables,
        ];

        if ($component->editingEditableIndex !== null && isset($component->editableDocuments[$component->editingEditableIndex])) {
            $component->editableDocuments[$component->editingEditableIndex] = $item;
        } else {
            $component->editableDocuments[] = $item;
        }

        $this->resetEditableModalState($component);
        $component->dispatch('equipment-type-modal-close', modalId: 'addEditableDocumentModal');
    }

    public function startEditEditableDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->editableDocuments[$index])) {
            return;
        }

        $item = $component->editableDocuments[$index];
        $component->editingEditableIndex = $index;
        $component->newEditableTitle = (string) ($item['title'] ?? '');
        $component->newEditableDescription = (string) ($item['description'] ?? '');
        $component->newEditableContent = (string) ($item['content'] ?? '');
        $component->newEditableVariables = (string) ($item['variables'] ?? '');
        $component->dispatch('equipment-type-modal-open', modalId: 'addEditableDocumentModal');
    }

    public function duplicateEditableDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->editableDocuments[$index])) {
            return;
        }

        $copy = $component->editableDocuments[$index];
        $copy['title'] = trim(((string) ($copy['title'] ?? '')) . ' (Copia)');
        $component->editableDocuments[] = $copy;
    }

    public function moveEditableDocumentUp(EquipmentTypeManagement $component, int $index): void
    {
        $component->editableDocuments = $this->moveItemUp($component->editableDocuments, $index);
    }

    public function moveEditableDocumentDown(EquipmentTypeManagement $component, int $index): void
    {
        $component->editableDocuments = $this->moveItemDown($component->editableDocuments, $index);
    }

    public function createTextFieldFromModal(EquipmentTypeManagement $component): void
    {
        $component->newTextLabel = $this->sanitizeText($component->newTextLabel, 150);
        $component->newTextPlaceholder = $this->sanitizeText($component->newTextPlaceholder, 180);

        $component->validate([
            'newTextLabel' => 'required|string|max:150',
            'newTextPlaceholder' => 'nullable|string|max:180',
            'newTextRequired' => 'boolean',
        ], [], [
            'newTextLabel' => trans('service-order::messages.equipment_type_field_label'),
        ]);

        $item = [
            'label' => $component->newTextLabel,
            'placeholder' => $component->newTextPlaceholder,
            'is_required' => (bool) $component->newTextRequired,
        ];

        if ($component->editingTextIndex !== null && isset($component->textFields[$component->editingTextIndex])) {
            $component->textFields[$component->editingTextIndex] = $item;
        } else {
            $component->textFields[] = $item;
        }

        $this->resetTextModalState($component);
        $component->dispatch('equipment-type-modal-close', modalId: 'addTextFieldModal');
    }

    public function startEditTextField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->textFields[$index])) {
            return;
        }

        $item = $component->textFields[$index];
        $component->editingTextIndex = $index;
        $component->newTextLabel = (string) ($item['label'] ?? '');
        $component->newTextPlaceholder = (string) ($item['placeholder'] ?? '');
        $component->newTextRequired = (bool) ($item['is_required'] ?? false);
        $component->dispatch('equipment-type-modal-open', modalId: 'addTextFieldModal');
    }

    public function duplicateTextField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->textFields[$index])) {
            return;
        }

        $copy = $component->textFields[$index];
        $copy['label'] = trim(((string) ($copy['label'] ?? '')) . ' (Copia)');
        $component->textFields[] = $copy;
    }

    public function moveTextFieldUp(EquipmentTypeManagement $component, int $index): void
    {
        $component->textFields = $this->moveItemUp($component->textFields, $index);
    }

    public function moveTextFieldDown(EquipmentTypeManagement $component, int $index): void
    {
        $component->textFields = $this->moveItemDown($component->textFields, $index);
    }

    public function createImageFieldFromModal(EquipmentTypeManagement $component): void
    {
        $component->newImageLabel = $this->sanitizeText($component->newImageLabel, 150);

        $component->validate([
            'newImageLabel' => 'required|string|max:150',
            'newImageRequired' => 'boolean',
        ], [], [
            'newImageLabel' => trans('service-order::messages.equipment_type_field_label'),
        ]);

        $item = [
            'id' => null,
            'label' => $component->newImageLabel,
            'is_required' => (bool) $component->newImageRequired,
            'file' => null,
            'existing_disk' => null,
            'existing_path' => null,
            'existing_original_name' => null,
            'existing_mime_type' => null,
            'existing_extension' => null,
            'existing_size' => null,
        ];

        if ($component->editingImageIndex !== null && isset($component->imageFields[$component->editingImageIndex])) {
            $existing = $component->imageFields[$component->editingImageIndex];
            $component->imageFields[$component->editingImageIndex] = array_merge($item, [
                'id' => $existing['id'] ?? null,
                'existing_disk' => $existing['existing_disk'] ?? null,
                'existing_path' => $existing['existing_path'] ?? null,
                'existing_original_name' => $existing['existing_original_name'] ?? null,
                'existing_mime_type' => $existing['existing_mime_type'] ?? null,
                'existing_extension' => $existing['existing_extension'] ?? null,
                'existing_size' => $existing['existing_size'] ?? null,
            ]);
        } else {
            $component->imageFields[] = $item;
        }

        $this->resetImageModalState($component);
        $component->dispatch('equipment-type-modal-close', modalId: 'addImageFieldModal');
    }

    public function startEditImageField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->imageFields[$index])) {
            return;
        }

        $item = $component->imageFields[$index];
        $component->editingImageIndex = $index;
        $component->newImageLabel = (string) ($item['label'] ?? '');
        $component->newImageRequired = (bool) ($item['is_required'] ?? false);
        $component->dispatch('equipment-type-modal-open', modalId: 'addImageFieldModal');
    }

    public function duplicateImageField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->imageFields[$index])) {
            return;
        }

        $copy = $component->imageFields[$index];
        $copy['id'] = null;
        $copy['label'] = trim(((string) ($copy['label'] ?? '')) . ' (Copia)');
        $component->imageFields[] = $copy;
    }

    public function moveImageFieldUp(EquipmentTypeManagement $component, int $index): void
    {
        $component->imageFields = $this->moveItemUp($component->imageFields, $index);
    }

    public function moveImageFieldDown(EquipmentTypeManagement $component, int $index): void
    {
        $component->imageFields = $this->moveItemDown($component->imageFields, $index);
    }

    public function removePdfDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->pdfDocuments[$index])) {
            return;
        }

        $file = $component->pdfDocuments[$index]['file'] ?? null;
        if ($file instanceof TemporaryUploadedFile
            && ! $this->isTemporaryFileUsedElsewhere($component->pdfDocuments, $index, $file)) {
            try {
                $file->delete();
            } catch (\Throwable $exception) {
            }
        }

        unset($component->pdfDocuments[$index]);
        $component->pdfDocuments = array_values($component->pdfDocuments);
    }

    public function removeEditableDocument(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->editableDocuments[$index])) {
            return;
        }

        unset($component->editableDocuments[$index]);
        $component->editableDocuments = array_values($component->editableDocuments);
    }

    public function removeTextField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->textFields[$index])) {
            return;
        }

        unset($component->textFields[$index]);
        $component->textFields = array_values($component->textFields);
    }

    public function removeImageField(EquipmentTypeManagement $component, int $index): void
    {
        if (! isset($component->imageFields[$index])) {
            return;
        }

        unset($component->imageFields[$index]);
        $component->imageFields = array_values($component->imageFields);
    }

    public function save(EquipmentTypeManagement $component): mixed
    {
        $this->sanitizeInputs($component);

        $component->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'isActive' => 'boolean',
        ], [], [
            'name' => trans('service-order::messages.equipment_type_name'),
            'description' => trans('service-order::messages.equipment_type_description'),
        ]);

        $this->validateCollections($component);

        if ($component->getErrorBag()->isNotEmpty()) {
            return null;
        }

        $data = [
            'name' => $component->name,
            'description' => $this->nullableText($component->description),
            'is_active' => (bool) $component->isActive,
        ];

        $documents = $this->buildDocumentsPayload($component);
        $fields = $this->buildFieldsPayload($component);
        $filesToDelete = $this->collectFilesToDelete($component);

        if ($component->mode === 'edit' && $component->equipmentTypeId) {
            $this->equipmentTypeService->updateEquipmentType($component->equipmentTypeId, $data, $documents, $fields, $filesToDelete);
        } else {
            $this->equipmentTypeService->createEquipmentType($data, $documents, $fields);
        }

        return redirect()->route('service-order-equipment-types-show');
    }

    private function initializeCreateState(EquipmentTypeManagement $component): void
    {
        $component->title = trans('service-order::messages.equipment_type_create_title');
        $component->mode = 'create';
        $component->equipmentTypeId = null;
        $component->name = '';
        $component->description = '';
        $component->isActive = true;
        $component->pdfDocuments = [];
        $component->editableDocuments = [];
        $component->textFields = [];
        $component->imageFields = [];
        $component->initialPdfFiles = [];
        $component->initialImageFiles = [];

        $this->resetPdfModalState($component);
        $this->resetEditableModalState($component);
        $this->resetTextModalState($component);
        $this->resetImageModalState($component);
    }

    private function resetPdfModalState(EquipmentTypeManagement $component): void
    {
        $component->newPdfTitle = '';
        $component->newPdfDescription = '';
        $component->newPdfFile = null;
        $component->newPdfTemporaryUrl = null;
        $component->editingPdfIndex = null;
        $component->resetValidation(['newPdfTitle', 'newPdfDescription', 'newPdfFile']);
    }

    private function resetEditableModalState(EquipmentTypeManagement $component): void
    {
        $component->newEditableTitle = '';
        $component->newEditableDescription = '';
        $component->newEditableContent = '';
        $component->newEditableVariables = '';
        $component->editingEditableIndex = null;
        $component->resetValidation(['newEditableTitle', 'newEditableDescription', 'newEditableContent', 'newEditableVariables']);
    }

    private function resetTextModalState(EquipmentTypeManagement $component): void
    {
        $component->newTextLabel = '';
        $component->newTextPlaceholder = '';
        $component->newTextRequired = false;
        $component->editingTextIndex = null;
        $component->resetValidation(['newTextLabel', 'newTextPlaceholder', 'newTextRequired']);
    }

    private function resetImageModalState(EquipmentTypeManagement $component): void
    {
        $component->newImageLabel = '';
        $component->newImageRequired = false;
        $component->editingImageIndex = null;
        $component->resetValidation(['newImageLabel', 'newImageRequired']);
    }

    private function sanitizeInputs(EquipmentTypeManagement $component): void
    {
        $component->name = $this->sanitizeText($component->name, 120);
        $component->description = $this->sanitizeText($component->description, 2000);

        $component->pdfDocuments = collect($component->pdfDocuments)
            ->map(function (array $item) {
                $item['title'] = $this->sanitizeText($item['title'] ?? '', 150);
                $item['description'] = $this->sanitizeText($item['description'] ?? '', 1000);

                return $item;
            })
            ->values()
            ->all();

        $component->editableDocuments = collect($component->editableDocuments)
            ->map(function (array $item) {
                $item['title'] = $this->sanitizeText($item['title'] ?? '', 150);
                $item['description'] = $this->sanitizeText($item['description'] ?? '', 1000);
                $item['content'] = $this->sanitizeText($item['content'] ?? '', 10000);
                $item['variables'] = $this->sanitizeText($item['variables'] ?? '', 1000);

                return $item;
            })
            ->values()
            ->all();

        $component->textFields = collect($component->textFields)
            ->map(function (array $item) {
                $item['label'] = $this->sanitizeText($item['label'] ?? '', 150);
                $item['placeholder'] = $this->sanitizeText($item['placeholder'] ?? '', 180);
                $item['is_required'] = (bool) ($item['is_required'] ?? false);

                return $item;
            })
            ->values()
            ->all();

        $component->imageFields = collect($component->imageFields)
            ->map(function (array $item) {
                $item['label'] = $this->sanitizeText($item['label'] ?? '', 150);
                $item['is_required'] = (bool) ($item['is_required'] ?? false);

                return $item;
            })
            ->values()
            ->all();
    }

    private function validateCollections(EquipmentTypeManagement $component): void
    {
        foreach ($component->pdfDocuments as $index => $document) {
            $isExistingFile = $this->hasExistingFile($document);
            $hasFile = ($document['file'] ?? null) instanceof TemporaryUploadedFile;
            $title = trim((string) ($document['title'] ?? ''));

            if ($title === '' || (! $isExistingFile && ! $hasFile)) {
                $component->addError("pdfDocuments.{$index}.title", trans('service-order::messages.equipment_type_pdf_title_required'));
            }

            if ($hasFile) {
                $component->validate([
                    "pdfDocuments.{$index}.file" => 'file|mimes:pdf|max:10240',
                ]);
            }
        }

        foreach ($component->editableDocuments as $index => $document) {
            $title = trim((string) ($document['title'] ?? ''));
            $content = trim((string) ($document['content'] ?? ''));

            if ($title === '') {
                $component->addError("editableDocuments.{$index}.title", trans('service-order::messages.equipment_type_template_title_required'));
            }

            if ($content === '') {
                $component->addError("editableDocuments.{$index}.content", trans('service-order::messages.equipment_type_template_content_required'));
            }
        }

        foreach ($component->textFields as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));

            if ($label === '') {
                $component->addError("textFields.{$index}.label", trans('service-order::messages.equipment_type_text_field_label_required'));
            }
        }

        foreach ($component->imageFields as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));

            if ($label === '') {
                $component->addError("imageFields.{$index}.label", trans('service-order::messages.equipment_type_image_field_label_required'));
            }
        }
    }

    private function buildDocumentsPayload(EquipmentTypeManagement $component): array
    {
        $documents = [];
        $sortOrder = 0;
        $temporaryFilesToDelete = [];

        foreach ($component->pdfDocuments as $document) {
            $disk = $document['existing_disk'] ?? null;
            $path = $document['existing_path'] ?? null;
            $originalName = $document['existing_original_name'] ?? null;
            $mimeType = $document['existing_mime_type'] ?? null;
            $extension = $document['existing_extension'] ?? null;
            $size = $document['existing_size'] ?? null;

            $file = $document['file'] ?? null;
            if ($file instanceof TemporaryUploadedFile) {
                $storedPath = $file->store('service-order/equipment-types/documents/pdfs', 'public');
                $disk = 'public';
                $path = $storedPath;
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $extension = strtolower((string) $file->getClientOriginalExtension());
                $size = $file->getSize();

                if (! in_array($file, $temporaryFilesToDelete, true)) {
                    $temporaryFilesToDelete[] = $file;
                }
            }

            $documents[] = [
                'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF,
                'title' => trim((string) $document['title']),
                'description' => $this->nullableText($document['description'] ?? ''),
                'disk' => $disk,
                'path' => $path,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size' => $size,
                'sort_order' => $sortOrder++,
            ];
        }

        foreach ($component->editableDocuments as $document) {
            $content = trim((string) ($document['content'] ?? ''));
            $documents[] = [
                'document_type' => ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE,
                'title' => trim((string) $document['title']),
                'description' => $this->nullableText($document['description'] ?? ''),
                'template_content' => $content,
                'variables_json' => $this->extractVariables($content, (string) ($document['variables'] ?? '')),
                'sort_order' => $sortOrder++,
            ];
        }

        foreach ($temporaryFilesToDelete as $temporaryFile) {
            try {
                $temporaryFile->delete();
            } catch (\Throwable $exception) {
            }
        }

        return $documents;
    }

    private function buildFieldsPayload(EquipmentTypeManagement $component): array
    {
        $fields = [];
        $sortOrder = 0;

        foreach ($component->textFields as $field) {
            $fields[] = [
                'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
                'label' => trim((string) $field['label']),
                'placeholder' => $this->nullableText($field['placeholder'] ?? ''),
                'default_text' => null,
                'is_required' => (bool) ($field['is_required'] ?? false),
                'sort_order' => $sortOrder++,
            ];
        }

        foreach ($component->imageFields as $field) {
            $fields[] = [
                'field_type' => ServiceOrderEquipmentTypeField::TYPE_IMAGE,
                'label' => trim((string) $field['label']),
                'is_required' => (bool) ($field['is_required'] ?? false),
                'disk' => null,
                'path' => null,
                'original_name' => null,
                'mime_type' => null,
                'extension' => null,
                'size' => null,
                'sort_order' => $sortOrder++,
            ];
        }

        return $fields;
    }

    private function collectFilesToDelete(EquipmentTypeManagement $component): array
    {
        if ($component->mode !== 'edit') {
            return [];
        }

        $currentPdfFiles = $this->collectCurrentFiles($component->pdfDocuments);
        $currentImageFiles = $this->collectCurrentFiles($component->imageFields);

        $toDelete = [];

        foreach ($component->initialPdfFiles as $initialFile) {
            if (! in_array($initialFile, $currentPdfFiles, true)) {
                $toDelete[] = $this->keyToFileMeta($initialFile);
            }
        }

        foreach ($component->initialImageFiles as $initialFile) {
            if (! in_array($initialFile, $currentImageFiles, true)) {
                $toDelete[] = $this->keyToFileMeta($initialFile);
            }
        }

        return $toDelete;
    }

    private function hasExistingFile(array $item): bool
    {
        $disk = trim((string) ($item['existing_disk'] ?? ''));
        $path = trim((string) ($item['existing_path'] ?? ''));

        return $disk !== '' && $path !== '';
    }

    private function sanitizeText(mixed $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    private function nullableText(mixed $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function extractVariables(string $content, string $variablesRaw): array
    {
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $content, $contentMatches);
        $fromContent = $contentMatches[1] ?? [];

        $fromInput = collect(explode(',', $variablesRaw))
            ->map(fn (string $item) => trim($item))
            ->filter(fn (string $item) => $item !== '')
            ->map(fn (string $item) => trim($item, '{} '))
            ->values()
            ->all();

        return collect(array_merge($fromContent, $fromInput))
            ->filter(fn (string $variable) => preg_match('/^[a-zA-Z0-9_]+$/', $variable) === 1)
            ->unique()
            ->values()
            ->all();
    }

    private function collectInitialFiles(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                if (! $this->hasExistingFile($item)) {
                    return null;
                }

                return $this->fileMetaToKey([
                    'disk' => $item['existing_disk'],
                    'path' => $item['existing_path'],
                ]);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function collectCurrentFiles(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                if (! $this->hasExistingFile($item)) {
                    return null;
                }

                return $this->fileMetaToKey([
                    'disk' => $item['existing_disk'],
                    'path' => $item['existing_path'],
                ]);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function fileMetaToKey(array $fileMeta): string
    {
        return trim((string) ($fileMeta['disk'] ?? '')) . '|' . trim((string) ($fileMeta['path'] ?? ''));
    }

    private function keyToFileMeta(string $key): array
    {
        [$disk, $path] = array_pad(explode('|', $key, 2), 2, '');

        return [
            'disk' => $disk,
            'path' => $path,
        ];
    }

    private function moveItemUp(array $items, int $index): array
    {
        if ($index <= 0 || ! isset($items[$index])) {
            return $items;
        }

        [$items[$index - 1], $items[$index]] = [$items[$index], $items[$index - 1]];

        return array_values($items);
    }

    private function moveItemDown(array $items, int $index): array
    {
        if (! isset($items[$index]) || ! isset($items[$index + 1])) {
            return $items;
        }

        [$items[$index + 1], $items[$index]] = [$items[$index], $items[$index + 1]];

        return array_values($items);
    }

    private function isTemporaryFileUsedElsewhere(array $documents, int $currentIndex, TemporaryUploadedFile $file): bool
    {
        foreach ($documents as $index => $document) {
            if ($index === $currentIndex) {
                continue;
            }

            if (($document['file'] ?? null) === $file) {
                return true;
            }
        }

        return false;
    }

    private function duplicateStoredFile(string $disk, string $path, string $targetDirectory): array
    {
        $resolvedDisk = trim($disk);
        $resolvedPath = trim($path);

        if ($resolvedDisk === '' || $resolvedPath === '') {
            return [];
        }

        $storage = Storage::disk($resolvedDisk);

        if (! $storage->exists($resolvedPath)) {
            return [];
        }

        $extension = pathinfo($resolvedPath, PATHINFO_EXTENSION);
        $suffix = $extension !== '' ? ".{$extension}" : '';
        $newPath = trim($targetDirectory, '/') . '/' . (string) Str::uuid() . $suffix;

        $storage->copy($resolvedPath, $newPath);

        return [
            'disk' => $resolvedDisk,
            'path' => $newPath,
        ];
    }
}
