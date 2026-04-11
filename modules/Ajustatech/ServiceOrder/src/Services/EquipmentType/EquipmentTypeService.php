<?php

namespace Ajustatech\ServiceOrder\Services\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipmentTypeService implements EquipmentTypeServiceInterface
{
    public function listEquipmentTypes(string $search = '', string $status = 'all', int $limitPerPage = 10): Collection
    {
        return ServiceOrderEquipmentType::listForIndex($search, $status, $limitPerPage);
    }

    public function findEquipmentType(string $id): ServiceOrderEquipmentType
    {
        return ServiceOrderEquipmentType::findWithDetailsOrFail($id);
    }

    public function createEquipmentType(array $data, array $documents, array $fields): ServiceOrderEquipmentType
    {
        return ServiceOrderEquipmentType::createWithDetails($data, $documents, $fields);
    }

    public function updateEquipmentType(string $id, array $data, array $documents, array $fields, array $filesToDelete): ServiceOrderEquipmentType
    {
        $equipmentType = ServiceOrderEquipmentType::findOrFailById($id);

        return $equipmentType->updateWithDetails($data, $documents, $fields, $filesToDelete);
    }

    public function deleteEquipmentType(string $id): void
    {
        $equipmentType = ServiceOrderEquipmentType::findWithDetailsOrFail($id);
        $equipmentType->deleteWithDetails();
    }

    public function toggleEquipmentTypeStatus(string $id): void
    {
        $equipmentType = ServiceOrderEquipmentType::findOrFailById($id);
        $equipmentType->toggleStatus();
    }

    public function duplicateEquipmentType(string $id): ServiceOrderEquipmentType
    {
        $equipmentType = ServiceOrderEquipmentType::findWithDetailsOrFail($id);
        $cloneName = $this->buildDuplicateName($equipmentType->name);

        $data = [
            'name' => $cloneName,
            'description' => $equipmentType->description,
            'is_active' => $equipmentType->is_active,
        ];

        $documents = $equipmentType->documents
            ->map(function (ServiceOrderEquipmentTypeDocument $document, int $index) {
                $fileMeta = $this->copyFileIfNeeded($document->disk, $document->path, 'service-order/equipment-types/documents');

                return [
                    'document_type' => $document->document_type,
                    'title' => $document->title,
                    'description' => $document->description,
                    'template_content' => $document->template_content,
                    'variables_json' => $document->variables_json,
                    'disk' => $fileMeta['disk'] ?? null,
                    'path' => $fileMeta['path'] ?? null,
                    'original_name' => $document->original_name,
                    'mime_type' => $document->mime_type,
                    'extension' => $document->extension,
                    'size' => $document->size,
                    'sort_order' => $index,
                ];
            })
            ->values()
            ->all();

        $fields = $equipmentType->fields
            ->map(function (ServiceOrderEquipmentTypeField $field, int $index) {
                $fileMeta = $this->copyFileIfNeeded($field->disk, $field->path, 'service-order/equipment-types/fields');

                return [
                    'field_type' => $field->field_type,
                    'label' => $field->label,
                    'placeholder' => $field->placeholder,
                    'default_text' => $field->default_text,
                    'is_required' => $field->is_required,
                    'disk' => $fileMeta['disk'] ?? null,
                    'path' => $fileMeta['path'] ?? null,
                    'original_name' => $field->original_name,
                    'mime_type' => $field->mime_type,
                    'extension' => $field->extension,
                    'size' => $field->size,
                    'sort_order' => $index,
                ];
            })
            ->values()
            ->all();

        return $equipmentType->duplicateWithDetails($data, $documents, $fields);
    }

    private function buildDuplicateName(string $originalName): string
    {
        $base = trim($originalName);

        if ($base === '') {
            return 'Tipo de equipamento (Copia)';
        }

        return "{$base} (Copia)";
    }

    private function copyFileIfNeeded(?string $disk, ?string $path, string $targetDirectory): array
    {
        $diskName = trim((string) $disk);
        $originalPath = trim((string) $path);

        if ($diskName === '' || $originalPath === '') {
            return [];
        }

        $storage = Storage::disk($diskName);

        if (! $storage->exists($originalPath)) {
            return [];
        }

        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $suffix = $extension !== '' ? ".{$extension}" : '';
        $newPath = trim($targetDirectory, '/') . '/' . (string) Str::uuid() . $suffix;

        $storage->copy($originalPath, $newPath);

        return [
            'disk' => $diskName,
            'path' => $newPath,
        ];
    }
}
