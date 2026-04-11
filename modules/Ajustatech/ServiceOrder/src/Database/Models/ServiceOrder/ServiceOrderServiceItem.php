<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderServiceItemFactory;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderServiceItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_service_items';

    protected $fillable = [
        'service_order_id',
        'procedure_id',
        'item_name',
        'item_notes',
        'unit_value',
        'discount_value',
        'total_value',
        'sort_order',
    ];

    protected $casts = [
        'unit_value' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderServiceItemFactory::new();
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderProcedure::class, 'procedure_id');
    }
}
