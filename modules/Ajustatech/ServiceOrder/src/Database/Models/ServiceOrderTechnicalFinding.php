<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderTechnicalFinding extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_technical_findings';

    protected $fillable = [
        'service_order_analysis_service_id',
        'service_order_analysis_question_id',
        'service_order_analysis_response_id',
        'description',
        'severity',
        'analysis_technical_action_id',
        'should_generate_budget',
        'generated_automatically',
        'consequence_snapshot',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'should_generate_budget' => 'boolean',
            'generated_automatically' => 'boolean',
            'consequence_snapshot' => 'array',
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

    public function response(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisResponse::class, 'service_order_analysis_response_id');
    }

    public function technicalAction(): BelongsTo
    {
        return $this->belongsTo(AnalysisTechnicalAction::class, 'analysis_technical_action_id');
    }
}

