<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisQuestionOptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisQuestionOption extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_question_options';

    protected $fillable = [
        'analysis_question_id',
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
        return $this->belongsTo(AnalysisQuestion::class, 'analysis_question_id');
    }

    protected static function newFactory()
    {
        return AnalysisQuestionOptionFactory::new();
    }
}
