<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderAnalysisComplementaryField extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_complementary_fields';

    protected $fillable = [
        'service_order_analysis_question_id',
        'source_complementary_field_id',
        'name',
        'label',
        'field_type',
        'sort_order',
        'is_required',
        'is_active',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'configuration' => 'array',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_question_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisComplementaryResponse::class, 'service_order_analysis_complementary_field_id');
    }
}

