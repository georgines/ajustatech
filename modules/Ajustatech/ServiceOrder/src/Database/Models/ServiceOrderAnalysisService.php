<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrderAnalysisServiceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderAnalysisService extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_services';

    protected $fillable = [
        'service_order_id',
        'analysis_type_id',
        'analysis_type_snapshot',
        'technician_id',
        'initial_notes',
        'status',
        'started_at',
        'completed_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'analysis_type_snapshot' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_type_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisSection::class, 'service_order_analysis_service_id')->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_service_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisResponse::class, 'service_order_analysis_service_id');
    }

    public function technicalFindings(): HasMany
    {
        return $this->hasMany(ServiceOrderTechnicalFinding::class, 'service_order_analysis_service_id');
    }

    protected static function newFactory()
    {
        return ServiceOrderAnalysisServiceFactory::new();
    }
}
