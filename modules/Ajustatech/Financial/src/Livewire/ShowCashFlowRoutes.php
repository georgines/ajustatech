<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Services\CashFlowRouteService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowCashFlowRoutes extends Component
{
    use SwitchAlertDispatch;

    public $title;
    public $routes = [];

    public function mount(CashFlowRouteService $service): void
    {
        $this->title = trans('financial::messages.cash_routes_title');
        $this->routes = $service->listRoutes();
    }

    public function confirmDelete(string $id): void
    {
        $this->dispatchConfirmation(trans('financial::messages.confirm_delete_cash_route'))
            ->to('delete-cash-route', id: $id)
            ->typeWarning()
            ->setButtonOK(trans('financial::messages.confirm_yes'))
            ->setButtonCancel(trans('financial::messages.confirm_no'))
            ->run();
    }

    #[On('delete-cash-route')]
    public function deleteCashRoute(string $id, CashFlowRouteService $service): void
    {
        $service->delete($id);
        $this->routes = $service->listRoutes();
    }

    public function flowLabel(string $flowKey): string
    {
        return match ($flowKey) {
            FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW => trans('financial::messages.cash_route_flow_payable_outflow'),
            FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW => trans('financial::messages.cash_route_flow_receivable_inflow'),
            FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW => trans('financial::messages.cash_route_flow_sales_open_outflow'),
            FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW => trans('financial::messages.cash_route_flow_sales_close_inflow'),
            default => $flowKey,
        };
    }

    public function render()
    {
        return view('financial::livewire.show-cash-flow-routes');
    }
}
