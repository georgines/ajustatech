<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Illuminate\Pagination\LengthAwarePaginator;

interface ServiceOrderRecordServiceInterface
{
    public function listServiceOrders(
        string $search = '',
        ?string $statusFlowId = null,
        ?string $openedFrom = null,
        ?string $openedTo = null,
        int $limitPerPage = 10
    ): LengthAwarePaginator;

    public function findServiceOrder(string $id): ServiceOrder;

    public function createServiceOrder(array $payload): ServiceOrder;

    public function updateServiceOrder(string $id, array $payload): ServiceOrder;

    public function refreshServiceOrderCustomerSnapshot(string $id): ServiceOrder;

    public function duplicateServiceOrder(string $id): ServiceOrder;

    public function deleteServiceOrder(string $id): void;
}
