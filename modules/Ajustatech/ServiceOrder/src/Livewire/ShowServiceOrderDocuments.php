<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrderDocuments extends Component
{
    public string $title = 'Documentos da Ordem';
    public string $orderId;

    public function mount(string $id): void
    {
        $this->orderId = $id;
    }

    public function render()
    {
        $order = ServiceOrder::findWithFieldValuesOrFail($this->orderId);
        $documents = $order->getDocumentFieldsWithValues();

        return view('service-order::livewire.show-service-order-documents', [
            'order' => $order,
            'documents' => $documents,
        ]);
    }
}
