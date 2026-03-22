<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrderServiceItemFactory;
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
        'service_catalog_service_id',
        'service_name',
        'quantity',
        'unit_price',
        'service_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'service_snapshot' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function catalogService(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogService::class, 'service_catalog_service_id');
    }

    protected static function newFactory()
    {
        return ServiceOrderServiceItemFactory::new();
    }
}

