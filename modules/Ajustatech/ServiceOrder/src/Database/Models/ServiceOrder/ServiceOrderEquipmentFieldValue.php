<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderEquipmentFieldValueFactory;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderEquipmentFieldValue extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_equipment_field_values';

    protected $fillable = [
        'service_order_id',
        'equipment_type_field_id',
        'field_type',
        'field_label',
        'field_placeholder',
        'is_required',
        'value_text',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ServiceOrderEquipmentFieldValueFactory::new();
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function equipmentTypeField(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentTypeField::class, 'equipment_type_field_id');
    }

    public static function syncForServiceOrder(ServiceOrder $serviceOrder, array $dynamicFields): void
    {
        static::query()
            ->where('service_order_id', $serviceOrder->id)
            ->delete();

        $rows = collect($dynamicFields)
            ->filter(fn (array $field) => ! blank($field['field_label'] ?? null))
            ->map(function (array $field) use ($serviceOrder) {
                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $serviceOrder->id,
                    'equipment_type_field_id' => $field['equipment_type_field_id'] ?? null,
                    'field_type' => $field['field_type'] ?? ServiceOrderEquipmentTypeField::TYPE_TEXT,
                    'field_label' => trim((string) ($field['field_label'] ?? '')),
                    'field_placeholder' => $field['field_placeholder'] ?? null,
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'value_text' => blank($field['value_text'] ?? null) ? null : trim((string) $field['value_text']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter(fn (array $field) => $field['field_label'] !== '')
            ->values()
            ->all();

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }

    public static function duplicateForServiceOrder(ServiceOrder $source, ServiceOrder $target): void
    {
        $fieldValues = $source->relationLoaded('fieldValues') ? $source->fieldValues : $source->fieldValues()->get();

        $rows = $fieldValues
            ->map(function (self $fieldValue) use ($target) {
                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $target->id,
                    'equipment_type_field_id' => $fieldValue->equipment_type_field_id,
                    'field_type' => $fieldValue->field_type,
                    'field_label' => $fieldValue->field_label,
                    'field_placeholder' => $fieldValue->field_placeholder,
                    'is_required' => $fieldValue->is_required,
                    'value_text' => $fieldValue->value_text,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }
}
