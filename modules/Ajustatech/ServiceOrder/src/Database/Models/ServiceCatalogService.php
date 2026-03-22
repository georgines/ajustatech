<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\ServiceCatalogServiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCatalogService extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_catalog_services';

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(ServiceOrderServiceItem::class, 'service_catalog_service_id');
    }

    protected static function newFactory()
    {
        return ServiceCatalogServiceFactory::new();
    }
}

