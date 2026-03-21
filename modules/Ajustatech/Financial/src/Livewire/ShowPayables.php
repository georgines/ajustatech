<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowPayables extends Component
{
    public $title;
    public $payables = [];

    public function mount(FinancialFlowService $service): void
    {
        $this->title = trans('financial::messages.payables_title');
        $this->payables = $service->listPayables();
    }

    public function settle(string $id, FinancialFlowService $service): void
    {
        $service->settlePayable($id);
        $this->payables = $service->listPayables();
    }

    public function render()
    {
        return view('financial::livewire.show-payables');
    }
}