<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrderAnalysisResponse extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_responses';

    protected $fillable = [
        'service_order_analysis_service_id',
        'service_order_analysis_question_id',
        'answered_by_user_id',
        'answer_text',
        'answer_number',
        'answer_date',
        'answer_boolean',
        'answer_json',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'answer_number' => 'decimal:4',
            'answer_date' => 'date',
            'answer_boolean' => 'boolean',
            'answer_json' => 'array',
            'answered_at' => 'datetime',
        ];
    }

    public function analysisService(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisService::class, 'service_order_analysis_service_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_question_id');
    }

    public function complementaryResponses(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisComplementaryResponse::class, 'service_order_analysis_response_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisAttachment::class, 'service_order_analysis_response_id');
    }
}

