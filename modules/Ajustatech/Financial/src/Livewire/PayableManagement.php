<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class PayableManagement extends Component
{
    public $title;
    public $counterpartyName = '';
    public $description = '';
    public $amount;
    public $dueDate;

    public function mount(): void
    {
        $this->title = trans('financial::messages.payable_create_title');
        $this->dueDate = now()->toDateString();
    }

    public function save(FinancialFlowService $service)
    {
        $this->validate([
            'counterpartyName' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|gt:0',
            'dueDate' => 'required|date',
        ]);

        $service->createPayable(
            $this->counterpartyName,
            (float) $this->amount,
            $this->dueDate,
            $this->description ?: null
        );

        return redirect()->route('financial-payables-show');
    }

    public function render()
    {
        return view('financial::livewire.payable-management');
    }
}