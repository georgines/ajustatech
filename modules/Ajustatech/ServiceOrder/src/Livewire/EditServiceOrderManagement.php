<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class EditServiceOrderManagement extends Component
{
    public string $title = 'Editar Ordem de Servico';
    public string $orderId;

    public function mount(string $id): void
    {
        $order = ServiceOrder::query()->findOrFail($id);
        $this->orderId = $order->id;
    }

    public function render()
    {
        $order = ServiceOrder::query()->findOrFail($this->orderId);

        return view('service-order::livewire.edit-service-order-management', [
            'order' => $order,
        ]);
    }
}

