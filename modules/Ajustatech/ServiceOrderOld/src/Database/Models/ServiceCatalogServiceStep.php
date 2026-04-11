<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\ServiceCatalogServiceStepFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCatalogServiceStep extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_catalog_service_steps';

    protected $fillable = [
        'service_catalog_service_id',
        'name',
        'sort_order',
        'is_required',
        'help_text',
        'technician_report_label',
        'requires_image_proof',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'requires_image_proof' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogService::class, 'service_catalog_service_id');
    }

    protected static function newFactory()
    {
        return ServiceCatalogServiceStepFactory::new();
    }
}

