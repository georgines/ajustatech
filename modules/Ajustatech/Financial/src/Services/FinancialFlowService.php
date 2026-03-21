<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Database\Models\FinancialPayable;
use Ajustatech\Financial\Database\Models\FinancialReceivable;
use Ajustatech\Financial\Database\Models\SalesCashSession;
use Ajustatech\Financial\Exceptions\InsufficientBalanceException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinancialFlowService
{
    public function __construct(private readonly CashFlowRouteService $routeService)
    {
    }

    public function createPayable(string $counterpartyName, float $amount, string $dueDate, ?string $description = null): FinancialPayable
    {
        $this->ensurePositiveAmount($amount);

        $managerialCashId = $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW);

        return FinancialPayable::create([
            'counterparty_name' => $counterpartyName,
            'description' => $description,
            'amount' => $amount,
            'due_date' => $dueDate,
            'company_cash_id' => $managerialCashId,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
        ]);
    }

    public function settlePayable(string $payableId): FinancialPayable
    {
        return DB::transaction(function () use ($payableId) {
            $payable = FinancialPayable::findOrFail($payableId);

            if ($payable->status !== 'pending') {
                return $payable;
            }

            $routeCashId = $this->resolveCashIdForPayableSettlement($payable);
            $cash = CompanyCash::findOrFail($routeCashId);
            $this->guardManagerialCash($cash);

            if (!$cash->hasSufficientBalance($payable->amount)) {
                $balance = $cash->getBalance();
                throw new InsufficientBalanceException($payable->amount, $balance ? $balance->balance : 0);
            }

            $cash->registerOutflow(
                $payable->amount,
                trans('financial::messages.payable_cash_outflow_description', [
                    'counterparty' => $payable->counterparty_name,
                    'amount' => $payable->amount,
                ])
            );

            $payable->update([
                'company_cash_id' => $routeCashId,
                'status' => 'paid',
                'cash_flow_status' => 'completed',
                'settled_at' => Carbon::now(),
            ]);

            return $payable->fresh();
        });
    }

    public function createReceivable(string $counterpartyName, float $amount, string $dueDate, string $paymentMethodType, ?string $description = null): FinancialReceivable
    {
        $this->ensurePositiveAmount($amount);

        $managerialCashId = $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW, $paymentMethodType);

        return FinancialReceivable::create([
            'counterparty_name' => $counterpartyName,
            'description' => $description,
            'amount' => $amount,
            'due_date' => $dueDate,
            'payment_method_type' => $paymentMethodType,
            'company_cash_id' => $managerialCashId,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
        ]);
    }

    public function settleReceivable(string $receivableId): FinancialReceivable
    {
        return DB::transaction(function () use ($receivableId) {
            $receivable = FinancialReceivable::findOrFail($receivableId);

            if ($receivable->status !== 'pending') {
                return $receivable;
            }

            $routeCashId = $this->resolveCashIdForReceivableSettlement($receivable);
            $cash = CompanyCash::findOrFail($routeCashId);
            $this->guardManagerialCash($cash);

            $cash->registerInflow(
                $receivable->amount,
                trans('financial::messages.receivable_cash_inflow_description', [
                    'counterparty' => $receivable->counterparty_name,
                    'amount' => $receivable->amount,
                ])
            );

            $receivable->update([
                'company_cash_id' => $routeCashId,
                'status' => 'received',
                'cash_flow_status' => 'completed',
                'settled_at' => Carbon::now(),
            ]);

            return $receivable->fresh();
        });
    }

    public function openDailySalesCash(int $userId, string $paymentMethodType, float $openingAmount): SalesCashSession
    {
        $this->ensurePositiveAmount($openingAmount);

        if ($this->getOpenSalesCashForUserToday($userId)) {
            throw new InvalidArgumentException(trans('financial::messages.sales_cash_already_opened_today'));
        }

        return DB::transaction(function () use ($userId, $paymentMethodType, $openingAmount) {
            $sourceCashId = $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW, $paymentMethodType);
            $destinationCashId = $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW, $paymentMethodType);

            $cash = CompanyCash::findOrFail($sourceCashId);
            $this->guardManagerialCash($cash);

            if (!$cash->hasSufficientBalance($openingAmount)) {
                $balance = $cash->getBalance();
                throw new InsufficientBalanceException($openingAmount, $balance ? $balance->balance : 0);
            }

            $cash->registerOutflow(
                $openingAmount,
                trans('financial::messages.sales_cash_open_outflow_description', ['amount' => $openingAmount, 'user_id' => $userId])
            );

            return SalesCashSession::create([
                'user_id' => $userId,
                'source_company_cash_id' => $sourceCashId,
                'destination_company_cash_id' => $destinationCashId,
                'opening_payment_method_type' => $paymentMethodType,
                'business_date' => Carbon::today(),
                'opening_amount' => $openingAmount,
                'opened_at' => Carbon::now(),
                'status' => 'open',
                'outflow_status' => 'completed',
                'inflow_status' => 'pending',
            ]);
        });
    }

    public function closeDailySalesCash(int $userId, string $paymentMethodType, float $closingAmount): SalesCashSession
    {
        $this->ensurePositiveAmount($closingAmount);

        return DB::transaction(function () use ($userId, $paymentMethodType, $closingAmount) {
            $session = $this->getOpenSalesCashForUserToday($userId);

            if (!$session) {
                throw new InvalidArgumentException(trans('financial::messages.sales_cash_not_opened_today'));
            }

            $destinationCashId = $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW, $paymentMethodType);
            $cash = CompanyCash::findOrFail($destinationCashId);
            $this->guardManagerialCash($cash);

            $cash->registerInflow(
                $closingAmount,
                trans('financial::messages.sales_cash_close_inflow_description', ['amount' => $closingAmount, 'user_id' => $userId])
            );

            $session->update([
                'destination_company_cash_id' => $destinationCashId,
                'closing_payment_method_type' => $paymentMethodType,
                'closing_amount' => $closingAmount,
                'closed_at' => Carbon::now(),
                'status' => 'closed',
                'inflow_status' => 'completed',
            ]);

            return $session->fresh();
        });
    }

    public function getOpenSalesCashForUserToday(int $userId): ?SalesCashSession
    {
        return SalesCashSession::query()
            ->where('user_id', $userId)
            ->whereDate('business_date', Carbon::today())
            ->where('status', 'open')
            ->first();
    }

    public function listPayables()
    {
        return FinancialPayable::query()->latest('due_date')->get();
    }

    public function listReceivables()
    {
        return FinancialReceivable::query()->latest('due_date')->get();
    }

    private function guardManagerialCash(CompanyCash $cash): void
    {
        if (!(bool) $cash->is_managerial) {
            throw new InvalidArgumentException(trans('financial::messages.only_managerial_cash_allowed'));
        }
    }

    private function ensurePositiveAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException(trans('financial::messages.transfer_amount_must_be_positive'));
        }
    }

    private function resolveCashIdForPayableSettlement(FinancialPayable $payable): string
    {
        try {
            return $this->routeService->resolveCashId(FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW);
        } catch (InvalidArgumentException) {
            return $payable->company_cash_id;
        }
    }

    private function resolveCashIdForReceivableSettlement(FinancialReceivable $receivable): string
    {
        try {
            return $this->routeService->resolveCashId(
                FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
                $receivable->payment_method_type
            );
        } catch (InvalidArgumentException) {
            return $receivable->company_cash_id;
        }
    }
}
