<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FinancialCashFlowRouteFactory extends Factory
{
    protected $model = FinancialCashFlowRoute::class;

    public function definition(): array
    {
        $flowKey = Arr::random([
            FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
            FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW,
        ]);

        return [
            'id' => (string) Str::uuid(),
            'flow_key' => $flowKey,
            'payment_method_type' => $flowKey === FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW
                ? null
                : Arr::random([
                    FinancialPaymentMethod::TYPE_DINHEIRO,
                    FinancialPaymentMethod::TYPE_PIX,
                    FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
                    FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
                ]),
            'company_cash_id' => CompanyCash::factory()->managerial(),
            'is_active' => true,
        ];
    }
}
