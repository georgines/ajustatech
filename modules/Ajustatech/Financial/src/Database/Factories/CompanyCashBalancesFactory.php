<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\CompanyCashBalances;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyCashBalancesFactory extends Factory
{
    protected $model = CompanyCashBalances::class;

    public function definition(): array
    {
        $totalInflows = $this->faker->randomFloat(2, 100, 15000);
        $totalOutflows = $this->faker->randomFloat(2, 0, $totalInflows);

        return [
            'id' => (string) Str::uuid(),
            'company_cash_id' => CompanyCash::factory(),
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'balance' => round($totalInflows - $totalOutflows, 2),
        ];
    }
}
