<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrderFieldValueFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderFieldValue extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_field_values';

    protected $fillable = [
        'service_order_id',
        'equipment_type_field_id',
        'field_slug',
        'field_type',
        'value_text',
        'value_json',
        'field_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'value_json' => 'array',
            'field_snapshot' => 'array',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function equipmentTypeField(): BelongsTo
    {
        return $this->belongsTo(EquipmentTypeField::class, 'equipment_type_field_id');
    }

    protected static function newFactory()
    {
        return ServiceOrderFieldValueFactory::new();
    }
}

