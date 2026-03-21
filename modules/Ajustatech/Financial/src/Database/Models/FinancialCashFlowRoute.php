<?php

namespace Ajustatech\Financial\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialCashFlowRoute extends Model
{
    use HasUuids;

    public const FLOW_PAYABLE_OUTFLOW = 'payable_outflow';
    public const FLOW_RECEIVABLE_INFLOW = 'receivable_inflow';
    public const FLOW_SALES_OPEN_OUTFLOW = 'sales_open_outflow';
    public const FLOW_SALES_CLOSE_INFLOW = 'sales_close_inflow';

    protected $table = 'financial_cash_flow_routes';

    protected $fillable = [
        'flow_key',
        'payment_method_type',
        'company_cash_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function companyCash(): BelongsTo
    {
        return $this->belongsTo(CompanyCash::class, 'company_cash_id');
    }
}