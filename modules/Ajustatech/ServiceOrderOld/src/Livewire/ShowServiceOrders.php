<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrders extends Component
{
    use SwitchAlertDispatch;

    public string $title = 'Ordens de Servico';

    public function confirmCancelOrder(string $id): void
    {
        $order = ServiceOrder::findOrFailById($id);

        if ($order->status === 'canceled') {
            return;
        }

        $this->dispatchConfirmation('Confirma cancelar esta ordem de servico?')
            ->typeWarning()
            ->setButtonOK('Sim')
            ->setButtonCancel('Nao')
            ->to('service-order-confirm-cancel-order', $id)
            ->run();
    }

    #[On('service-order-confirm-cancel-order')]
    public function cancelOrder(string $id): void
    {
        $order = ServiceOrder::findOrFailById($id);
        if ($order->status === 'canceled') {
            return;
        }

        $order->cancel();
    }

    public function render()
    {
        $orders = ServiceOrder::getLatestListingWithServiceItems(100);

        return view('service-order::livewire.show-service-orders', [
            'orders' => $orders,
            'statusLabels' => [
                'open' => 'Aberta',
                'canceled' => 'Cancelada',
                'completed' => 'Concluida',
            ],
        ]);
    }
}
