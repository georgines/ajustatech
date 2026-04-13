<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderRecordServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceOrderRecordService implements ServiceOrderRecordServiceInterface
{
    public function listServiceOrders(
        string $search = '',
        ?string $statusFlowId = null,
        ?string $openedFrom = null,
        ?string $openedTo = null,
        int $limitPerPage = 10
    ): LengthAwarePaginator {
        $limit = in_array($limitPerPage, [10, 30, 50, 100], true) ? $limitPerPage : 10;

        return ServiceOrder::listForIndex($search, $statusFlowId, $openedFrom, $openedTo, $limit);
    }

    public function findServiceOrder(string $id): ServiceOrder
    {
        return ServiceOrder::findDetailedOrFail($id);
    }

    public function createServiceOrder(array $payload): ServiceOrder
    {
        return ServiceOrder::createFromPayload($payload);
    }

    public function updateServiceOrder(string $id, array $payload): ServiceOrder
    {
        return ServiceOrder::updateFromPayloadById($id, $payload);
    }

    public function refreshServiceOrderCustomerSnapshot(string $id): ServiceOrder
    {
        return ServiceOrder::refreshCustomerSnapshotById($id);
    }

    public function duplicateServiceOrder(string $id): ServiceOrder
    {
        return ServiceOrder::findDetailedOrFail($id)->duplicateWithRelations();
    }

    public function deleteServiceOrder(string $id): void
    {
        ServiceOrder::deleteById($id);
    }
}
