<?php

namespace Ajustatech\ServiceOrderOld\ViewModels;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Support\DocumentVariableCatalog;
use Ajustatech\ServiceOrderOld\Support\EquipmentFieldType;

class EquipmentTypeEditorViewModel
{
    public static function fromModel(EquipmentType $equipmentType): array
    {
        $equipmentType->loadMissing('fields.options');

        return [
            'screen' => 'equipment_type_editor',
            'vuexy' => [
                'layout' => 'card-with-tabs',
                'sections' => [
                    'general_data' => ['name', 'description', 'is_active'],
                    'dynamic_fields' => ['sortable_repeater'],
                ],
                'switches' => ['is_required', 'is_printable', 'is_active'],
            ],
            'equipment_type' => [
                'id' => $equipmentType->id,
                'name' => $equipmentType->name,
                'description' => $equipmentType->description,
                'is_active' => $equipmentType->is_active,
            ],
            'available_field_types' => EquipmentFieldType::values(),
            'document_variables' => DocumentVariableCatalog::toUiList()->values()->all(),
            'fields' => $equipmentType->fields
                ->sortBy('sort_order')
                ->values()
                ->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'name' => $field->name,
                        'slug' => $field->slug,
                        'field_type' => $field->field_type,
                        'sort_order' => $field->sort_order,
                        'is_required' => $field->is_required,
                        'is_printable' => $field->is_printable,
                        'is_active' => $field->is_active,
                        'configuration' => $field->configuration ?? [],
                        'options' => $field->options->sortBy('sort_order')->values()->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'sort_order' => $option->sort_order,
                            'is_active' => $option->is_active,
                        ])->all(),
                        'vuexy_component' => self::mapComponentForFieldType($field->field_type),
                    ];
                })
                ->all(),
        ];
    }

    private static function mapComponentForFieldType(string $fieldType): string
    {
        return match ($fieldType) {
            EquipmentFieldType::PHOTO => 'photo-card-preview',
            EquipmentFieldType::TEXT => 'text-input-or-textarea',
            EquipmentFieldType::SELECT => 'vuexy-select',
            EquipmentFieldType::RADIO => 'vuexy-radio-group',
            EquipmentFieldType::FILE => 'file-upload-with-preview',
            EquipmentFieldType::DOCUMENT => 'large-document-textarea',
            default => 'unsupported',
        };
    }
}

