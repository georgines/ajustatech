<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Services\CashFlowRouteService;
use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Ajustatech\Financial\Services\PaymentMethodService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class CashFlowRouteManagement extends Component
{
    public $title;
    public $mode = 'create';
    public $routeId = null;

    public $flowKey = '';
    public $paymentMethodType = '';
    public $companyCashId = '';

    public $flowOptions = [];
    public $paymentMethodTypes = [];
    public $managerialCashes = [];

    public function mount(CashFlowRouteService $service, PaymentMethodService $paymentMethodService, ?string $id = null): void
    {
        $this->title = trans('financial::messages.cash_route_create_title');
        $this->flowOptions = $this->getFlowOptions($service);
        $this->paymentMethodTypes = $paymentMethodService->getTypes();

        $cashService = app(CompanyCashServiceInterface::class);
        $this->managerialCashes = $cashService::getAllCompanyCashs()
            ->where('is_managerial', true)
            ->values()
            ->all();

        if ($id) {
            $route = $service->find($id);
            $this->mode = 'edit';
            $this->routeId = $route->id;
            $this->flowKey = $route->flow_key;
            $this->paymentMethodType = $route->payment_method_type ?? '';
            $this->companyCashId = $route->company_cash_id;
            $this->title = trans('financial::messages.cash_route_edit_title');
        }
    }

    public function save(CashFlowRouteService $service)
    {
        $rules = [
            'flowKey' => 'required|string',
            'companyCashId' => 'required|exists:company_cashes,id',
        ];

        if ($this->requiresPaymentMethodType()) {
            $rules['paymentMethodType'] = 'required|string';
        }

        $this->validate($rules);

        $service->upsert(
            $this->flowKey,
            $this->requiresPaymentMethodType() ? $this->paymentMethodType : null,
            $this->companyCashId,
            $this->routeId
        );

        return redirect()->route('financial-cash-routes-show');
    }

    public function updatedFlowKey(): void
    {
        if (!$this->requiresPaymentMethodType()) {
            $this->paymentMethodType = '';
        }
    }

    public function requiresPaymentMethodType(): bool
    {
        return in_array($this->flowKey, [
            FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
            FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW,
        ], true);
    }

    public function cashFieldLabel(): string
    {
        if (in_array($this->flowKey, [
            FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
        ], true)) {
            return trans('financial::messages.cash_route_cash_source');
        }

        return trans('financial::messages.cash_route_cash_destination');
    }

    private function getFlowOptions(CashFlowRouteService $service): array
    {
        $labels = [
            FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW => trans('financial::messages.cash_route_flow_payable_outflow'),
            FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW => trans('financial::messages.cash_route_flow_receivable_inflow'),
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW => trans('financial::messages.cash_route_flow_sales_open_outflow'),
            FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW => trans('financial::messages.cash_route_flow_sales_close_inflow'),
        ];

        return collect($service->flowKeys())
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
            ])
            ->all();
    }

    public function render()
    {
        return view('financial::livewire.cash-flow-route-management');
    }
}
