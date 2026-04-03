<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\AnalysisConsequenceFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisConsequence extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_consequences';

    protected $fillable = [
        'analysis_question_id',
        'analysis_question_option_id',
        'match_operator',
        'match_value',
        'severity',
        'description',
        'analysis_technical_action_id',
        'should_generate_budget',
        'visible_to_technician',
        'recommendation_text',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'should_generate_budget' => 'boolean',
            'visible_to_technician' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AnalysisQuestion::class, 'analysis_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(AnalysisQuestionOption::class, 'analysis_question_option_id');
    }

    public function technicalAction(): BelongsTo
    {
        return $this->belongsTo(AnalysisTechnicalAction::class, 'analysis_technical_action_id');
    }

    protected static function newFactory()
    {
        return AnalysisConsequenceFactory::new();
    }
}
