<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethodCost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FinancialPaymentMethodCostFactory extends Factory
{
    protected $model = FinancialPaymentMethodCost::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'financial_payment_method_id' => FinancialPaymentMethod::factory(),
            'fixed_cost' => $this->faker->randomFloat(2, 0, 10),
            'percent_cost' => $this->faker->randomFloat(4, 0, 8),
            'brand' => null,
            'installments' => null,
            'receipt_channel' => null,
        ];
    }

    public function cardRule(): static
    {
        return $this->state(fn () => [
            'brand' => Arr::random(['Visa', 'Mastercard', 'Elo']),
            'installments' => $this->faker->numberBetween(1, 12),
            'receipt_channel' => Arr::random(['maquina', 'telefone', 'link']),
        ]);
    }
}
