<?php

namespace Ajustatech\Financial\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPayable extends Model
{
    use HasUuids;

    protected $table = 'financial_payables';

    protected $fillable = [
        'counterparty_name',
        'description',
        'amount',
        'due_date',
        'company_cash_id',
        'status',
        'cash_flow_status',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'due_date' => 'date',
            'settled_at' => 'datetime',
        ];
    }

    public function companyCash(): BelongsTo
    {
        return $this->belongsTo(CompanyCash::class, 'company_cash_id');
    }
}