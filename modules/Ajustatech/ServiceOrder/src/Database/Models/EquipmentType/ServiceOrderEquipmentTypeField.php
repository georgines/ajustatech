<?php

namespace Ajustatech\ServiceOrder\Database\Models\EquipmentType;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentType\ServiceOrderEquipmentTypeFieldFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeField extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';

    protected $table = 'service_order_equipment_type_fields';

    protected $fillable = [
        'equipment_type_id',
        'field_type',
        'label',
        'placeholder',
        'default_text',
        'is_required',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderEquipmentTypeFieldFactory::new();
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentType::class, 'equipment_type_id');
    }

    public function toServiceOrderDynamicField(): array
    {
        return [
            'equipment_type_field_id' => (string) $this->id,
            'field_type' => (string) $this->field_type,
            'field_label' => (string) $this->label,
            'field_placeholder' => (string) ($this->placeholder ?? ''),
            'is_required' => (bool) $this->is_required,
            'value_text' => (string) ($this->default_text ?? ''),
        ];
    }

    public static function createManyForEquipmentType(string $equipmentTypeId, array $fields): void
    {
        if (empty($fields)) {
            return;
        }

        $rows = collect($fields)
            ->map(function (array $field, int $index) use ($equipmentTypeId) {
                return [
                    'id' => (string) Str::uuid(),
                    'equipment_type_id' => $equipmentTypeId,
                    'field_type' => $field['field_type'],
                    'label' => $field['label'],
                    'placeholder' => $field['placeholder'] ?? null,
                    'default_text' => $field['default_text'] ?? null,
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'disk' => $field['disk'] ?? null,
                    'path' => $field['path'] ?? null,
                    'original_name' => $field['original_name'] ?? null,
                    'mime_type' => $field['mime_type'] ?? null,
                    'extension' => $field['extension'] ?? null,
                    'size' => $field['size'] ?? null,
                    'sort_order' => $field['sort_order'] ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        static::query()->insert($rows);
    }
}
