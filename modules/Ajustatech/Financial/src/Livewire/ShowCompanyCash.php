<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowCompanyCash extends Component
{
    public $title;
    public $cashs;

    public function mount()
    {
        $this->title = trans('financial::messages.title');
        $cashServices = app(CompanyCashServiceInterface::class);
        $this->cashs = $cashServices::getAllCompanyCashs();
    }

    public function render()
    {
        return view('financial::livewire.show-company-cash');
    }
}
