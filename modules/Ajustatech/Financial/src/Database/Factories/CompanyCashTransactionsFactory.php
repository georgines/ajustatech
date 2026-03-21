<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\CompanyCashTransactions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyCashTransactionsFactory extends Factory
{
    protected $model = CompanyCashTransactions::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'company_cash_id' => CompanyCash::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 5000),
            'description' => $this->faker->sentence(),
            'hash' => (string) Str::uuid(),
            'category' => $this->faker->optional()->word(),
            'is_inflow' => $this->faker->boolean(),
        ];
    }

    public function inflow(): static
    {
        return $this->state(fn () => ['is_inflow' => true]);
    }

    public function outflow(): static
    {
        return $this->state(fn () => ['is_inflow' => false]);
    }
}
