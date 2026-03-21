<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class SalesCashDailyManagement extends Component
{
    public $title;
    public $managerialCashId = '';
    public $openingAmount;
    public $closingAmount;
    public $managerialCashes = [];
    public $currentSession = null;

    public function mount(FinancialFlowService $service): void
    {
        $this->title = trans('financial::messages.sales_cash_title');
        $this->managerialCashes = $service->getManagerialCashes();

        $user = Auth::user();
        if ($user) {
            $this->currentSession = $service->getOpenSalesCashForUserToday((int) $user->id);
        }
    }

    public function openCash(FinancialFlowService $service): void
    {
        $this->validate([
            'managerialCashId' => 'required|exists:company_cashes,id',
            'openingAmount' => 'required|numeric|gt:0',
        ]);

        $user = Auth::user();
        if (!$user) {
            $this->addError('openingAmount', trans('financial::messages.user_not_authenticated'));
            return;
        }

        $this->currentSession = $service->openDailySalesCash(
            (int) $user->id,
            $this->managerialCashId,
            (float) $this->openingAmount
        );

        $this->openingAmount = null;
    }

    public function closeCash(FinancialFlowService $service): void
    {
        $this->validate([
            'closingAmount' => 'required|numeric|gt:0',
        ]);

        $user = Auth::user();
        if (!$user) {
            $this->addError('closingAmount', trans('financial::messages.user_not_authenticated'));
            return;
        }

        $service->closeDailySalesCash((int) $user->id, (float) $this->closingAmount);
        $this->currentSession = null;
        $this->closingAmount = null;
    }

    public function render()
    {
        return view('financial::livewire.sales-cash-daily-management');
    }
}