<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialPayable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FinancialPayableFactory extends Factory
{
    protected $model = FinancialPayable::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'counterparty_name' => $this->faker->company(),
            'description' => $this->faker->optional()->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'due_date' => $this->faker->dateTimeBetween('-5 days', '+45 days'),
            'company_cash_id' => CompanyCash::factory()->managerial(),
            'status' => 'pending',
            'cash_flow_status' => 'pending',
            'settled_at' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'cash_flow_status' => 'completed',
            'settled_at' => Carbon::now(),
        ]);
    }
}
