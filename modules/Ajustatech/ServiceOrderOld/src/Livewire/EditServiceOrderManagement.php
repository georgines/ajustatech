<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class EditServiceOrderManagement extends Component
{
    public string $title = 'Editar Ordem de Servico';
    public string $orderId;

    public function mount(string $id): void
    {
        $this->orderId = $id;
    }

    public function render()
    {
        $order = ServiceOrder::findOrFailById($this->orderId);

        return view('service-order::livewire.edit-service-order-management', [
            'order' => $order,
        ]);
    }
}
