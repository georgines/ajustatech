<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Ajustatech\Financial\Services\PaymentMethodService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class SalesCashDailyManagement extends Component
{
    public $title;
    public $openingPaymentMethodType = '';
    public $closingPaymentMethodType = '';
    public $openingAmount;
    public $closingAmount;
    public $paymentMethodTypes = [];
    public $currentSession = null;

    public function mount(FinancialFlowService $service, PaymentMethodService $paymentMethodService): void
    {
        $this->title = trans('financial::messages.sales_cash_title');
        $this->paymentMethodTypes = $paymentMethodService->getTypes();

        $user = Auth::user();
        if ($user) {
            $this->currentSession = $service->getOpenSalesCashForUserToday((int) $user->id);
            if ($this->currentSession && $this->currentSession->opening_payment_method_type) {
                $this->closingPaymentMethodType = $this->currentSession->opening_payment_method_type;
            }
        }
    }

    public function openCash(FinancialFlowService $service): void
    {
        $this->validate([
            'openingPaymentMethodType' => 'required|string',
            'openingAmount' => 'required|numeric|gt:0',
        ]);

        $user = Auth::user();
        if (!$user) {
            $this->addError('openingAmount', trans('financial::messages.user_not_authenticated'));
            return;
        }

        $this->currentSession = $service->openDailySalesCash(
            (int) $user->id,
            $this->openingPaymentMethodType,
            (float) $this->openingAmount
        );

        $this->closingPaymentMethodType = $this->openingPaymentMethodType;
        $this->openingAmount = null;
    }

    public function closeCash(FinancialFlowService $service): void
    {
        $this->validate([
            'closingPaymentMethodType' => 'required|string',
            'closingAmount' => 'required|numeric|gt:0',
        ]);

        $user = Auth::user();
        if (!$user) {
            $this->addError('closingAmount', trans('financial::messages.user_not_authenticated'));
            return;
        }

        $service->closeDailySalesCash((int) $user->id, $this->closingPaymentMethodType, (float) $this->closingAmount);
        $this->currentSession = null;
        $this->closingAmount = null;
        $this->closingPaymentMethodType = '';
    }

    public function render()
    {
        return view('financial::livewire.sales-cash-daily-management');
    }
}