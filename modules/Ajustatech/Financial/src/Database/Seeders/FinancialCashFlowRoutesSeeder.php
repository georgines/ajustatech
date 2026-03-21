<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Illuminate\Database\Seeder;

class FinancialCashFlowRoutesSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $managerialCash = $this->ensureDefaultManagerialCash();

        $routes = [
            [FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW, null],
            [FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW, FinancialPaymentMethod::TYPE_DINHEIRO],
            [FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW, FinancialPaymentMethod::TYPE_PIX],
            [FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW, FinancialPaymentMethod::TYPE_CARTAO_DEBITO],
            [FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW, FinancialPaymentMethod::TYPE_CARTAO_CREDITO],
            [FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW, FinancialPaymentMethod::TYPE_DINHEIRO],
            [FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW, FinancialPaymentMethod::TYPE_PIX],
            [FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW, FinancialPaymentMethod::TYPE_DINHEIRO],
            [FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW, FinancialPaymentMethod::TYPE_PIX],
        ];

        foreach ($routes as [$flowKey, $paymentMethodType]) {
            FinancialCashFlowRoute::updateOrCreate(
                [
                    'flow_key' => $flowKey,
                    'payment_method_type' => $paymentMethodType,
                ],
                [
                    'company_cash_id' => $managerialCash->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
