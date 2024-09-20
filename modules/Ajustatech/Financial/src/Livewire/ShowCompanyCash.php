<?php

namespace Ajustatech\Financial\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowCompanyCash extends Component
{
    public $title;

    public function mount()
    {
        $this->title = trans('financial::messages.title');
    }

    public function render()
    {
        return view('financial::livewire.show-company-cash');
    }
}
