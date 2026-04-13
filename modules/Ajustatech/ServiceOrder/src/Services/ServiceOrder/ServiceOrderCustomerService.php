<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCustomerServiceInterface;

class ServiceOrderCustomerService implements ServiceOrderCustomerServiceInterface
{
    public function searchCustomers(string $search = '', int $limit = 15): array
    {
        return Customer::searchForServiceOrder($search, $limit)
            ->map(fn (Customer $customer) => $customer->toServiceOrderSummary())
            ->all();
    }

    public function findCustomerSummary(string $id): ?array
    {
        return Customer::findForServiceOrder($id)?->toServiceOrderSummary();
    }

    public function findCustomerSelection(string $id): ?array
    {
        return Customer::findForServiceOrder($id)?->toServiceOrderSelection();
    }

    public function findCustomerSelectionOrFail(string $id): array
    {
        return Customer::findForServiceOrderOrFail($id)->toServiceOrderSelection();
    }

    public function createCustomer(array $payload): array
    {
        return Customer::createForServiceOrder($payload)->toServiceOrderSummary();
    }

    public function findCustomerForEditingOrFail(string $id): Customer
    {
        return Customer::findOrFail($id);
    }
}
