<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ServiceOrderServiceInterface
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

    public function listStatusFlows(): Collection;

    public function listActiveEquipmentTypes(): Collection;

    public function listProcedures(): Collection;

    public function listAnalysisServices(): Collection;

    public function searchCustomers(string $search = '', int $limit = 15): Collection;
}
