<?php

namespace Ajustatech\ServiceOrderOld\Database\Models;

use Ajustatech\ServiceOrderOld\Database\Factories\AnalysisTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AnalysisType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_types';

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

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (!$model->slug) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(AnalysisSection::class, 'analysis_type_id')->orderBy('sort_order');
    }

    public function analysisServices(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisService::class, 'analysis_type_id');
    }

    protected static function newFactory()
    {
        return AnalysisTypeFactory::new();
    }
}
