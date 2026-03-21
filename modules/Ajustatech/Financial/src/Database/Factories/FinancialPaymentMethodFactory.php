<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FinancialPaymentMethodFactory extends Factory
{
    protected $model = FinancialPaymentMethod::class;

    public function definition(): array
    {
        $type = Arr::random([
            FinancialPaymentMethod::TYPE_DINHEIRO,
            FinancialPaymentMethod::TYPE_PIX,
            FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
            FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
        ]);

        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'name' => $this->nameFromType($type),
            'is_active' => true,
        ];
    }

    private function nameFromType(string $type): string
    {
        return match ($type) {
            FinancialPaymentMethod::TYPE_DINHEIRO => 'Dinheiro',
            FinancialPaymentMethod::TYPE_PIX => 'Pix',
            FinancialPaymentMethod::TYPE_CARTAO_DEBITO => 'Cartao Debito',
            FinancialPaymentMethod::TYPE_CARTAO_CREDITO => 'Cartao Credito',
            default => ucfirst($type),
        };
    }
}
