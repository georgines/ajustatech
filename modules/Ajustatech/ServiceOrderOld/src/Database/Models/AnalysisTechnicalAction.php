<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisTechnicalActionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisTechnicalAction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_technical_actions';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(AnalysisTechnicalActionPrice::class, 'analysis_technical_action_id');
    }

    protected static function newFactory()
    {
        return AnalysisTechnicalActionFactory::new();
    }
}
