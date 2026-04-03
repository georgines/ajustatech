<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceOrderAnalysisQuestion extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_analysis_questions';

    protected $fillable = [
        'service_order_analysis_service_id',
        'service_order_analysis_section_id',
        'source_question_id',
        'question_code',
        'prompt',
        'help_text',
        'technician_note_label',
        'answer_type',
        'sort_order',
        'is_required',
        'is_repeatable',
        'requires_photo_evidence',
        'repetition_group',
        'repetition_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_repeatable' => 'boolean',
            'requires_photo_evidence' => 'boolean',
            'repetition_index' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function analysisService(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisService::class, 'service_order_analysis_service_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderAnalysisSection::class, 'service_order_analysis_section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisQuestionOption::class, 'service_order_analysis_question_id')->orderBy('sort_order');
    }

    public function complementaryFields(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisComplementaryField::class, 'service_order_analysis_question_id')->orderBy('sort_order');
    }

    public function response(): HasOne
    {
        return $this->hasOne(ServiceOrderAnalysisResponse::class, 'service_order_analysis_question_id');
    }
}
