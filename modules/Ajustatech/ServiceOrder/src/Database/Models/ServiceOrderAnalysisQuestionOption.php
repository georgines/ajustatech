<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderAnalysisQuestionOption extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_question_options';

    protected $fillable = [
        'service_order_analysis_question_id',
        'source_option_id',
        'label',
        'value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisQuestion::class, 'service_order_analysis_question_id');
    }
}

