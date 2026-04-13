<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

use Ajustatech\Customer\Database\Models\Customer;

interface ServiceOrderCustomerServiceInterface
{
    public function searchCustomers(string $search = '', int $limit = 15): array;

    public function findCustomerSummary(string $id): ?array;

    public function findCustomerSelection(string $id): ?array;

    public function findCustomerSelectionOrFail(string $id): array;

    public function createCustomer(array $payload): array;

    public function findCustomerForEditingOrFail(string $id): Customer;
}
