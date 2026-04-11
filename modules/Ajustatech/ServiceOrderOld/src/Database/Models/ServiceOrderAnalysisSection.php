<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderAnalysisSection extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_sections';

    protected $fillable = [
        'service_order_analysis_service_id',
        'source_section_id',
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function analysisService(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisService::class, 'service_order_analysis_service_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_section_id')->orderBy('sort_order');
    }
}

