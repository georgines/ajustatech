<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'equipment_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(EquipmentTypeField::class, 'equipment_type_id');
    }

    public function activeFields(): HasMany
    {
        return $this->fields()->where('is_active', true)->orderBy('sort_order');
    }

    protected static function newFactory()
    {
        return EquipmentTypeFactory::new();
    }
}

