<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_orders';

    protected $fillable = [
        'equipment_type_id',
        'customer_id',
        'customer_name',
        'equipment_name',
        'brand',
        'model',
        'serial_number',
        'entry_date',
        'reported_issue',
        'status',
        'equipment_type_snapshot',
        'fields_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'equipment_type_snapshot' => 'array',
            'fields_snapshot' => 'array',
        ];
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ServiceOrderFieldValue::class, 'service_order_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceOrderAttachment::class, 'service_order_id');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceOrderServiceItem::class, 'service_order_id');
    }

    protected static function newFactory()
    {
        return ServiceOrderFactory::new();
    }
}
