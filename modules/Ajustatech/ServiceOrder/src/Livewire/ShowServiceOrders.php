<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrders extends Component
{
    public string $title = 'Ordens de Servico';

    public function render()
    {
        $orders = ServiceOrder::query()
            ->with('serviceItems')
            ->latest()
            ->limit(100)
            ->get();

        return view('service-order::livewire.show-service-orders', [
            'orders' => $orders,
        ]);
    }
}
