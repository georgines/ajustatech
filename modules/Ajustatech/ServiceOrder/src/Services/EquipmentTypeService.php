<?php

namespace Ajustatech\ServiceOrder\Services;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrder\Support\EquipmentFieldConfigurationValidator;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EquipmentTypeService
{
    public function __construct(
        private readonly EquipmentFieldConfigurationValidator $fieldConfigurationValidator
    ) {
    }

    public function create(array $payload): EquipmentType
    {
        $validated = $this->validateEquipmentTypePayload($payload);

        return DB::transaction(function () use ($validated) {
            $equipmentType = EquipmentType::createFromPayload([
                'name' => Arr::get($validated, 'name'),
                'description' => Arr::get($validated, 'description'),
                'is_active' => Arr::get($validated, 'is_active', true),
            ]);

            $this->syncFields($equipmentType, Arr::get($validated, 'fields', []));

            return $equipmentType->fresh('fields.options');
        });
    }

    public function update(string $equipmentTypeId, array $payload): EquipmentType
    {
        $equipmentType = EquipmentType::findOrFailById($equipmentTypeId);
        $validated = $this->validateEquipmentTypePayload($payload, true);

        return DB::transaction(function () use ($equipmentType, $validated) {
            $equipmentType->update([
                'name' => Arr::get($validated, 'name'),
                'description' => Arr::get($validated, 'description'),
                'is_active' => Arr::get($validated, 'is_active', true),
            ]);

            $this->syncFields($equipmentType, Arr::get($validated, 'fields', []));

            return $equipmentType->fresh('fields.options');
        });
    }

    public function reorderFields(string $equipmentTypeId, array $orderedFieldIds): void
    {
        $equipmentType = EquipmentType::findOrFailById($equipmentTypeId);
        $currentFields = $equipmentType->getFieldIds();

        if (count($currentFields) !== count($orderedFieldIds)) {
            throw ValidationException::withMessages([
                'ordered_field_ids' => 'All field ids must be provided for manual ordering.',
            ]);
        }

        foreach ($orderedFieldIds as $index => $fieldId) {
            if (!in_array($fieldId, $currentFields, true)) {
                throw ValidationException::withMessages([
                    'ordered_field_ids' => 'A field id does not belong to the selected equipment type.',
                ]);
            }

            EquipmentTypeField::updateSortOrderWithinEquipmentType($equipmentType->id, $fieldId, $index + 1);
        }
    }

    public function buildActiveFieldSnapshots(EquipmentType|string $equipmentType): array
    {
        $equipmentType = $equipmentType instanceof EquipmentType
            ? $equipmentType->load([
                'fields' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'options' => fn ($optionQuery) => $optionQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ]),
            ])
            : EquipmentType::findWithActiveFieldsAndOptionsOrFail($equipmentType);

        return $equipmentType->fields
            ->values()
            ->map(function (EquipmentTypeField $field) {
                return [
                    'id' => $field->id,
                    'field_type' => $field->field_type,
                    'name' => $field->name,
                    'slug' => $field->slug,
                    'sort_order' => $field->sort_order,
                    'is_required' => $field->is_required,
                    'is_printable' => $field->is_printable,
                    'is_active' => $field->is_active,
                    'configuration' => $field->configuration ?? [],
                    'options' => $field->options
                        ->values()
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'sort_order' => $option->sort_order,
                        ])
                        ->all(),
                ];
            })
            ->all();
    }

    private function validateEquipmentTypePayload(array $payload, bool $isUpdating = false): array
    {
        $validated = Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.id' => [$isUpdating ? 'sometimes' : 'nullable', 'string', 'uuid'],
            'fields.*.field_type' => ['required', 'string'],
            'fields.*.name' => ['required', 'string', 'max:255'],
            'fields.*.slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'fields.*.sort_order' => ['required', 'integer', 'min:1'],
            'fields.*.is_required' => ['required', 'boolean'],
            'fields.*.is_printable' => ['required', 'boolean'],
            'fields.*.is_active' => ['required', 'boolean'],
            'fields.*.configuration' => ['nullable', 'array'],
            'fields.*.options' => ['nullable', 'array'],
        ])->validate();

        $fields = collect(Arr::get($validated, 'fields', []));
        $this->ensureDistinctFieldSlugsAndOrders($fields);

        $fields->each(function (array $field) {
            $this->fieldConfigurationValidator->validate($field);
        });

        return $validated;
    }

    private function syncFields(EquipmentType $equipmentType, array $fields): void
    {
        $existingFields = $equipmentType->getFieldsWithOptionsKeyedById();
        $keptFieldIds = [];

        foreach ($fields as $fieldPayload) {
            $fieldId = Arr::get($fieldPayload, 'id');
            if (!$fieldId) {
                $fieldId = $existingFields
                    ->first(fn (EquipmentTypeField $item) => $item->slug === Arr::get($fieldPayload, 'slug'))
                    ?->id;
            }

            $this->ensureSlugIsUniqueInsideEquipmentType(
                $equipmentType->id,
                Arr::get($fieldPayload, 'slug'),
                $fieldId
            );

            $field = $fieldId && $existingFields->has($fieldId)
                ? $existingFields->get($fieldId)
                : new EquipmentTypeField(['equipment_type_id' => $equipmentType->id]);

            $field->fill([
                'field_type' => Arr::get($fieldPayload, 'field_type'),
                'name' => Arr::get($fieldPayload, 'name'),
                'slug' => Arr::get($fieldPayload, 'slug'),
                'sort_order' => Arr::get($fieldPayload, 'sort_order'),
                'is_required' => Arr::get($fieldPayload, 'is_required', false),
                'is_printable' => Arr::get($fieldPayload, 'is_printable', false),
                'is_active' => Arr::get($fieldPayload, 'is_active', true),
                'configuration' => Arr::get($fieldPayload, 'configuration', []),
            ]);
            $field->equipment_type_id = $equipmentType->id;
            $field->save();

            $this->syncFieldOptions($field, Arr::get($fieldPayload, 'options', []));
            $keptFieldIds[] = $field->id;
        }

        if (!empty($keptFieldIds)) {
            $equipmentType->deleteFieldsNotIn($keptFieldIds);
            return;
        }

        $equipmentType->deleteAllFields();
    }

    private function syncFieldOptions(EquipmentTypeField $field, array $options): void
    {
        if (!EquipmentFieldType::acceptsOptions($field->field_type)) {
            $field->deleteAllOptions();
            return;
        }

        $field->deleteAllOptions();
        foreach ($options as $index => $optionPayload) {
            $field->createOption([
                'label' => Arr::get($optionPayload, 'label'),
                'value' => Arr::get($optionPayload, 'value'),
                'sort_order' => Arr::get($optionPayload, 'sort_order', $index + 1),
                'is_active' => Arr::get($optionPayload, 'is_active', true),
            ]);
        }
    }

    private function ensureDistinctFieldSlugsAndOrders(Collection $fields): void
    {
        $slugCount = $fields->pluck('slug')->countBy()->filter(fn (int $count) => $count > 1)->keys()->all();
        if (!empty($slugCount)) {
            throw ValidationException::withMessages([
                'fields' => 'Duplicate slugs are not allowed inside an equipment type.',
            ]);
        }

        $orderCount = $fields->pluck('sort_order')->countBy()->filter(fn (int $count) => $count > 1)->keys()->all();
        if (!empty($orderCount)) {
            throw ValidationException::withMessages([
                'fields' => 'Duplicate sort order values are not allowed inside an equipment type.',
            ]);
        }
    }

    private function ensureSlugIsUniqueInsideEquipmentType(string $equipmentTypeId, string $slug, ?string $ignoreFieldId = null): void
    {
        if (EquipmentTypeField::slugExistsInEquipmentType($equipmentTypeId, $slug, $ignoreFieldId)) {
            throw ValidationException::withMessages([
                'fields' => 'The slug "' . $slug . '" is already used in this equipment type.',
            ]);
        }
    }
}
