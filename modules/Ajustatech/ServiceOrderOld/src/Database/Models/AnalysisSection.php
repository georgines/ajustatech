<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisSectionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisSection extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_sections';

    protected $fillable = [
        'analysis_type_id',
        'name',
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

    public function analysisType(): BelongsTo
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_type_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AnalysisQuestion::class, 'analysis_section_id')->orderBy('sort_order');
    }

    protected static function newFactory()
    {
        return AnalysisSectionFactory::new();
    }
}
