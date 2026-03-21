<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ReceivableManagement extends Component
{
    public $title;
    public $counterpartyName = '';
    public $description = '';
    public $amount;
    public $dueDate;
    public $managerialCashId = '';
    public $managerialCashes = [];

    public function mount(FinancialFlowService $service): void
    {
        $this->title = trans('financial::messages.receivable_create_title');
        $this->dueDate = now()->toDateString();
        $this->managerialCashes = $service->getManagerialCashes();
    }

    public function save(FinancialFlowService $service)
    {
        $this->validate([
            'counterpartyName' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|gt:0',
            'dueDate' => 'required|date',
            'managerialCashId' => 'required|exists:company_cashes,id',
        ]);

        $service->createReceivable(
            $this->counterpartyName,
            (float) $this->amount,
            $this->dueDate,
            $this->managerialCashId,
            $this->description ?: null
        );

        return redirect()->route('financial-receivables-show');
    }

    public function render()
    {
        return view('financial::livewire.receivable-management');
    }
}