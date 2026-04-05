<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrder extends Component
{
    public $title;

    public function mount()
    {
        $this->title = trans('service-order::messages.title');
    }

    public function render()
    {
        return view('service-order::livewire.service-order.show-service-order');
    }
}
