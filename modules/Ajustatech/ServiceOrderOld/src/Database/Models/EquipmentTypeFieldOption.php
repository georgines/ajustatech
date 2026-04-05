<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\EquipmentTypeFieldOptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentTypeFieldOption extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'equipment_type_field_options';

    protected $fillable = [
        'equipment_type_field_id',
        'label',
        'value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(EquipmentTypeField::class, 'equipment_type_field_id');
    }

    protected static function newFactory()
    {
        return EquipmentTypeFieldOptionFactory::new();
    }
}

