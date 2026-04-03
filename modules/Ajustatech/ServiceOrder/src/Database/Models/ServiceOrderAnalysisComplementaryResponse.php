<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderAnalysisComplementaryResponse extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_complementary_responses';

    protected $fillable = [
        'service_order_analysis_response_id',
        'service_order_analysis_complementary_field_id',
        'value_text',
        'value_number',
        'value_date',
        'value_boolean',
        'value_json',
    ];

    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:4',
            'value_date' => 'date',
            'value_boolean' => 'boolean',
            'value_json' => 'array',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisResponse::class, 'service_order_analysis_response_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisComplementaryField::class, 'service_order_analysis_complementary_field_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisAttachment::class, 'service_order_analysis_complementary_response_id');
    }
}

