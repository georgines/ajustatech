<?php

namespace Ajustatech\ServiceOrderOld\Services;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EquipmentTypeFormService
{
    public function hydrateFieldsForEdit(EquipmentType $equipmentType): array
    {
        return $equipmentType->fields
            ->sortBy('sort_order')
            ->values()
            ->map(function ($field) {
                return [
                    'id' => $field->id,
                    'field_type' => $field->field_type,
                    'name' => $field->name,
                    'slug' => $field->slug,
                    'sort_order' => $field->sort_order,
                    'is_required' => (bool) $field->is_required,
                    'is_printable' => (bool) $field->is_printable,
                    'is_active' => (bool) $field->is_active,
                    'configuration' => $field->configuration ?? [],
                    'options' => $field->options
                        ->sortBy('sort_order')
                        ->values()
                        ->map(fn ($option) => [
                            'label' => $option->label,
                            'value' => $option->value,
                            'sort_order' => (int) $option->sort_order,
                            'is_active' => (bool) $option->is_active,
                        ])
                        ->all(),
                ];
            })
            ->all();
    }

    public function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $index => $field) {
            $type = (string) Arr::get($field, 'field_type', EquipmentFieldType::TEXT);
            $name = trim((string) Arr::get($field, 'name', ''));
            $slugInput = trim((string) Arr::get($field, 'slug', ''));

            $item = [
                'id' => Arr::get($field, 'id'),
                'field_type' => $type,
                'name' => $name,
                'slug' => $slugInput !== '' ? Str::slug($slugInput, '_') : Str::slug($name, '_'),
                'sort_order' => $index + 1,
                'is_required' => (bool) Arr::get($field, 'is_required', false),
                'is_printable' => in_array($type, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true)
                    ? false
                    : (bool) Arr::get($field, 'is_printable', false),
                'is_active' => (bool) Arr::get($field, 'is_active', true),
                'configuration' => Arr::get($field, 'configuration', []),
                'options' => [],
            ];

            if (EquipmentFieldType::acceptsOptions($type)) {
                $item['options'] = collect(Arr::get($field, 'options', []))
                    ->values()
                    ->map(function (array $option, int $optionIndex) {
                        $label = trim((string) Arr::get($option, 'label', ''));
                        $value = trim((string) Arr::get($option, 'value', ''));

                        return [
                            'label' => $label,
                            'value' => $value !== '' ? Str::slug($value, '_') : Str::slug($label, '_'),
                            'sort_order' => $optionIndex + 1,
                            'is_active' => (bool) Arr::get($option, 'is_active', true),
                        ];
                    })
                    ->all();
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    public function defaultConfigurationForType(string $type): array
    {
        return match ($type) {
            EquipmentFieldType::PHOTO => [
                'max_files' => 1,
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            ],
            EquipmentFieldType::TEXT => [
                'placeholder' => '',
                'help' => '',
                'max_length' => 500,
            ],
            EquipmentFieldType::FILE => [
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'preview_mode' => 'modal',
            ],
            EquipmentFieldType::DOCUMENT => [
                'template' => 'Cliente: {{cliente_nome}}',
                'help' => 'Use variaveis disponiveis para montagem do documento final.',
            ],
            default => [],
        };
    }
}
