<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderAnalysisAttachment extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_attachments';

    protected $fillable = [
        'service_order_analysis_question_id',
        'service_order_analysis_response_id',
        'service_order_analysis_complementary_response_id',
        'service_order_analysis_complementary_field_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisResponse::class, 'service_order_analysis_response_id');
    }

    public function complementaryResponse(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisComplementaryResponse::class, 'service_order_analysis_complementary_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_question_id');
    }
}

