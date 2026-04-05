<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisQuestionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisQuestion extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_questions';

    protected $fillable = [
        'analysis_section_id',
        'code',
        'prompt',
        'help_text',
        'technician_note_label',
        'answer_type',
        'sort_order',
        'is_required',
        'is_repeatable',
        'requires_photo_evidence',
        'repeat_source_question_id',
        'repeat_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_repeatable' => 'boolean',
            'requires_photo_evidence' => 'boolean',
            'repeat_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AnalysisSection::class, 'analysis_section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(AnalysisQuestionOption::class, 'analysis_question_id')->orderBy('sort_order');
    }

    public function complementaryFields(): HasMany
    {
        return $this->hasMany(AnalysisQuestionComplementaryField::class, 'analysis_question_id')->orderBy('sort_order');
    }

    public function consequences(): HasMany
    {
        return $this->hasMany(AnalysisConsequence::class, 'analysis_question_id');
    }

    protected static function newFactory()
    {
        return AnalysisQuestionFactory::new();
    }
}
