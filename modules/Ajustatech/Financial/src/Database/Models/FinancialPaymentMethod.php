<?php

namespace Ajustatech\Financial\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialPaymentMethod extends Model
{
    use HasUuids;

    public const TYPE_DINHEIRO = 'dinheiro';
    public const TYPE_PIX = 'pix';
    public const TYPE_CARTAO_DEBITO = 'cartao_debito';
    public const TYPE_CARTAO_CREDITO = 'cartao_credito';

    protected $table = 'financial_payment_methods';

    protected $fillable = [
        'type',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function costs(): HasMany
    {
        return $this->hasMany(FinancialPaymentMethodCost::class, 'financial_payment_method_id');
    }
}