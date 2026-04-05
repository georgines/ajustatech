<?php

namespace Ajustatech\ServiceOrderOld\ViewModels;

use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Support\EquipmentFieldType;
use Illuminate\Support\Arr;

class ServiceOrderDynamicFormViewModel
{
    public static function fromOrder(ServiceOrder $serviceOrder): array
    {
        $serviceOrder->loadMissing('fieldValues', 'attachments');
        $values = $serviceOrder->fieldValues->keyBy('field_slug');

        return [
            'screen' => 'service_order_open',
            'vuexy' => [
                'layout' => 'dynamic-card-form',
                'required_feedback' => true,
                'print_visibility_badge' => true,
            ],
            'service_order' => [
                'id' => $serviceOrder->id,
                'equipment_type' => $serviceOrder->equipment_type_snapshot,
                'status' => $serviceOrder->status,
            ],
            'fields' => collect($serviceOrder->fields_snapshot)
                ->sortBy('sort_order')
                ->values()
                ->map(function (array $field) use ($serviceOrder, $values) {
                    $slug = Arr::get($field, 'slug');
                    $fieldValue = $values->get($slug);

                    return [
                        'id' => Arr::get($field, 'id'),
                        'name' => Arr::get($field, 'name'),
                        'slug' => $slug,
                        'field_type' => Arr::get($field, 'field_type'),
                        'sort_order' => Arr::get($field, 'sort_order'),
                        'is_required' => Arr::get($field, 'is_required', false),
                        'is_printable' => Arr::get($field, 'is_printable', false),
                        'configuration' => Arr::get($field, 'configuration', []),
                        'options' => Arr::get($field, 'options', []),
                        'value' => $fieldValue?->value_json ?? $fieldValue?->value_text,
                        'attachments_count' => $serviceOrder->attachments->where('field_slug', $slug)->count(),
                        'vuexy_component' => self::mapComponentForFieldType((string) Arr::get($field, 'field_type')),
                        'internal_only' => EquipmentFieldType::isAttachment((string) Arr::get($field, 'field_type'))
                            || !Arr::get($field, 'is_printable', false),
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

