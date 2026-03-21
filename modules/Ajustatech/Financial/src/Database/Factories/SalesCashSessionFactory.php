<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Database\Models\SalesCashSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SalesCashSessionFactory extends Factory
{
    protected $model = SalesCashSession::class;

    public function definition(): array
    {
        $openedAt = Carbon::now()->subHour();

        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'source_company_cash_id' => CompanyCash::factory()->managerial(),
            'destination_company_cash_id' => CompanyCash::factory()->managerial(),
            'opening_payment_method_type' => Arr::random([
                FinancialPaymentMethod::TYPE_DINHEIRO,
                FinancialPaymentMethod::TYPE_PIX,
            ]),
            'closing_payment_method_type' => null,
            'business_date' => Carbon::today(),
            'opening_amount' => $this->faker->randomFloat(2, 10, 1000),
            'closing_amount' => null,
            'opened_at' => $openedAt,
            'closed_at' => null,
            'status' => 'open',
            'outflow_status' => 'completed',
            'inflow_status' => 'pending',
        ];
    }

    public function closed(): static
    {
        return $this->state(function () {
            $closingAmount = $this->faker->randomFloat(2, 10, 1000);

            return [
                'closing_payment_method_type' => Arr::random([
                    FinancialPaymentMethod::TYPE_DINHEIRO,
                    FinancialPaymentMethod::TYPE_PIX,
                ]),
                'closing_amount' => $closingAmount,
                'closed_at' => Carbon::now(),
                'status' => 'closed',
                'inflow_status' => 'completed',
            ];
        });
    }
}
