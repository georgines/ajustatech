<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('core::layouts.app')]
class ShowServiceOrder extends Component
{
    use WithPagination;

    public string $title = '';

    public string $search = '';

    public ?string $statusFlowId = null;

    public ?string $openedFrom = null;

    public ?string $openedTo = null;

    public int $limitePerPage = 10;

    public function mount(): void
    {
        $this->title = app()->getLocale() === 'en' ? 'Service Orders' : 'Ordens de Servico';
        $this->openedFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->openedTo = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 120);
        $this->resetPage();
    }

    public function updatedStatusFlowId(): void
    {
        $this->statusFlowId = blank($this->statusFlowId) ? null : $this->statusFlowId;
        $this->resetPage();
    }

    public function updatedOpenedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedOpenedTo(): void
    {
        $this->resetPage();
    }

    public function updatedLimitePerPage(): void
    {
        if (! in_array($this->limitePerPage, [10, 30, 50, 100], true)) {
            $this->limitePerPage = 10;
        }

        $this->resetPage();
    }

    public function deleteServiceOrder(string $id, ServiceOrderServiceInterface $service): void
    {
        $service->deleteServiceOrder($id);
        $this->resetPage();
    }

    public function duplicateServiceOrder(string $id, ServiceOrderServiceInterface $service): void
    {
        $service->duplicateServiceOrder($id);
        $this->resetPage();
    }

    public function render(ServiceOrderServiceInterface $service)
    {
        $workingDays = $service->workingDays();
        $holidays = $service->holidays();
        $serviceOrders = $service->listServiceOrders(
            $this->search,
            $this->statusFlowId,
            $this->openedFrom,
            $this->openedTo,
            $this->limitePerPage
        )->onEachSide(1);

        $rows = $serviceOrders->through(function ($serviceOrder) use ($workingDays, $holidays) {
            $snapshot = $serviceOrder->customer_snapshot_json ?? [];

            return [
                'id' => $serviceOrder->id,
                'order_number' => $serviceOrder->order_number,
                'opened_at' => $serviceOrder->opened_at,
                'business_days' => $serviceOrder->businessDaysSinceCreation(
                    $workingDays,
                    $holidays
                ),
                'customer_name' => $snapshot['name'] ?? $serviceOrder->customer?->name,
                'status_name' => $serviceOrder->statusFlow?->name,
                'status_code' => $serviceOrder->statusFlow?->code,
            ];
        });

        return view('service-order::livewire.service-order.show-service-order', [
            'serviceOrders' => $rows,
            'statusFlows' => $service->listStatusFlows(),
        ]);
    }
}
