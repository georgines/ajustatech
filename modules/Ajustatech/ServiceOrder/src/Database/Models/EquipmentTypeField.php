<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentTypeFieldFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentTypeField extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'equipment_type_fields';

    protected $fillable = [
        'equipment_type_id',
        'field_type',
        'name',
        'slug',
        'sort_order',
        'is_required',
        'is_printable',
        'is_active',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_printable' => 'boolean',
            'is_active' => 'boolean',
            'configuration' => 'array',
        ];
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(EquipmentTypeFieldOption::class, 'equipment_type_field_id');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true)->orderBy('sort_order');
    }

    public static function updateSortOrderWithinEquipmentType(string $equipmentTypeId, string $fieldId, int $sortOrder): void
    {
        static::query()
            ->where('id', $fieldId)
            ->where('equipment_type_id', $equipmentTypeId)
            ->update(['sort_order' => $sortOrder]);
    }

    public static function slugExistsInEquipmentType(string $equipmentTypeId, string $slug, ?string $ignoreFieldId = null): bool
    {
        $query = static::query()
            ->where('equipment_type_id', $equipmentTypeId)
            ->where('slug', $slug);

        if ($ignoreFieldId) {
            $query->where('id', '!=', $ignoreFieldId);
        }

        return $query->exists();
    }

    public function createOption(array $payload): EquipmentTypeFieldOption
    {
        return $this->options()->create($payload);
    }

    public function deleteAllOptions(): void
    {
        $this->options()->delete();
    }

    protected static function newFactory()
    {
        return EquipmentTypeFieldFactory::new();
    }
}
