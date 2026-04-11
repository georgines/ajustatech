<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderEquipmentFieldValueFactory;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
