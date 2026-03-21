<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\FinancialReceivableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReceivable extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'financial_receivables';

    protected $fillable = [
        'counterparty_name',
        'description',
        'amount',
        'due_date',
        'payment_method_type',
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

    protected static function newFactory()
    {
        return FinancialReceivableFactory::new();
    }
}
