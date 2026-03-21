<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use InvalidArgumentException;

class CashFlowRouteService
{
    public function listRoutes()
    {
        return FinancialCashFlowRoute::query()->with('companyCash')->latest()->get();
    }

    public function find(string $id): FinancialCashFlowRoute
    {
        return FinancialCashFlowRoute::findOrFail($id);
    }

    public function upsert(string $flowKey, ?string $paymentMethodType, string $companyCashId, ?string $id = null): FinancialCashFlowRoute
    {
        $cash = CompanyCash::findOrFail($companyCashId);

        if (!(bool) $cash->is_managerial) {
            throw new InvalidArgumentException(trans('financial::messages.only_managerial_cash_allowed'));
        }

        if ($id) {
            $route = $this->find($id);
            $route->update([
                'flow_key' => $flowKey,
                'payment_method_type' => $paymentMethodType,
                'company_cash_id' => $companyCashId,
                'is_active' => true,
            ]);
            return $route->fresh('companyCash');
        }

        $existing = FinancialCashFlowRoute::query()
            ->where('flow_key', $flowKey)
            ->where('payment_method_type', $paymentMethodType)
            ->first();

        if ($existing) {
            $existing->update([
                'company_cash_id' => $companyCashId,
                'is_active' => true,
            ]);
            return $existing->fresh('companyCash');
        }

        return FinancialCashFlowRoute::create([
            'flow_key' => $flowKey,
            'payment_method_type' => $paymentMethodType,
            'company_cash_id' => $companyCashId,
            'is_active' => true,
        ])->fresh('companyCash');
    }

    public function delete(string $id): void
    {
        $this->find($id)->delete();
    }

    public function resolveCashId(string $flowKey, ?string $paymentMethodType = null): string
    {
        $query = FinancialCashFlowRoute::query()
            ->where('flow_key', $flowKey)
            ->where('is_active', true);

        if ($paymentMethodType !== null) {
            $query->where('payment_method_type', $paymentMethodType);
        } else {
            $query->whereNull('payment_method_type');
        }

        $route = $query->first();

        if (!$route) {
            throw new InvalidArgumentException(trans('financial::messages.cash_route_not_configured'));
        }

        return $route->company_cash_id;
    }

    public function flowKeys(): array
    {
        return [
            FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
            FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW,
        ];
    }
}