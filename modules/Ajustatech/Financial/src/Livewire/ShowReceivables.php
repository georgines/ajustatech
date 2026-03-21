<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\FinancialFlowService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowReceivables extends Component
{
    public $title;
    public $receivables = [];

    public function mount(FinancialFlowService $service): void
    {
        $this->title = trans('financial::messages.receivables_title');
        $this->receivables = $service->listReceivables();
    }

    public function settle(string $id, FinancialFlowService $service): void
    {
        $service->settleReceivable($id);
        $this->receivables = $service->listReceivables();
    }

    public function render()
    {
        return view('financial::livewire.show-receivables');
    }
}