<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
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
        $order = ServiceOrder::query()->findOrFail($id);

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
        $order = ServiceOrder::query()->findOrFail($id);
        if ($order->status === 'canceled') {
            return;
        }

        $order->update(['status' => 'canceled']);
    }

    public function render()
    {
        $orders = ServiceOrder::query()
            ->with('serviceItems')
            ->latest()
            ->limit(100)
            ->get();

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
