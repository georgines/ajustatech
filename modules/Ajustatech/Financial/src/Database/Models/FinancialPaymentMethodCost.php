<?php

namespace Ajustatech\Financial\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPaymentMethodCost extends Model
{
    use HasUuids;

    protected $table = 'financial_payment_method_costs';

    protected $fillable = [
        'financial_payment_method_id',
        'fixed_cost',
        'percent_cost',
        'brand',
        'installments',
        'receipt_channel',
    ];

    protected function casts(): array
    {
        return [
            'fixed_cost' => 'float',
            'percent_cost' => 'float',
            'installments' => 'integer',
        ];
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(FinancialPaymentMethod::class, 'financial_payment_method_id');
    }
}