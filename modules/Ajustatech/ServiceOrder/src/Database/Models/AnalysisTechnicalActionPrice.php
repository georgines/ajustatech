<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisTechnicalActionPrice extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'analysis_technical_action_prices';

    protected $fillable = [
        'analysis_technical_action_id',
        'amount',
        'currency',
        'valid_from',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'valid_from' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(AnalysisTechnicalAction::class, 'analysis_technical_action_id');
    }
}

