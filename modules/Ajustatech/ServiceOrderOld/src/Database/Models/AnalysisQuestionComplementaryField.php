<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisQuestionComplementaryFieldFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisQuestionComplementaryField extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_question_complementary_fields';

    protected $fillable = [
        'analysis_question_id',
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
        return $this->belongsTo(AnalysisQuestion::class, 'analysis_question_id');
    }

    protected static function newFactory()
    {
        return AnalysisQuestionComplementaryFieldFactory::new();
    }
}
