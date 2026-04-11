<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderEquipmentFieldValue;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderServiceItem;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceOrderService implements ServiceOrderServiceInterface
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
        return ServiceOrder::query()
            ->with([
                'statusFlow:id,name,code',
                'customer',
                'equipmentType:id,name',
                'equipmentType.documents:id,equipment_type_id,document_type,title,template_content,path,disk,original_name,variables_json',
                'equipmentType.fields:id,equipment_type_id,field_type,label,placeholder,is_required,default_text',
                'selectedDocument:id,title,document_type,template_content,path,disk,original_name,variables_json',
                'serviceItems:id,service_order_id,procedure_id,item_name,item_notes,unit_value,discount_value,total_value,sort_order',
                'fieldValues:id,service_order_id,equipment_type_field_id,field_type,field_label,field_placeholder,is_required,value_text',
            ])
            ->findOrFail($id);
    }

    public function createServiceOrder(array $payload): ServiceOrder
    {
        return DB::transaction(function () use ($payload) {
            $customer = Customer::query()->findOrFail($payload['customer_id']);

            $serviceOrder = ServiceOrder::query()->create([
                'customer_id' => $customer->id,
                'equipment_type_id' => $payload['equipment_type_id'] ?: null,
                'selected_document_id' => $payload['selected_document_id'] ?: null,
                'equipment_brand' => $payload['equipment_brand'] ?: null,
                'equipment_model' => $payload['equipment_model'] ?: null,
                'equipment_serial_number' => $payload['equipment_serial_number'] ?: null,
                'customer_snapshot_json' => $this->buildCustomerSnapshot($customer),
                'opened_at' => now(),
            ]);

            $this->syncFieldValues($serviceOrder, $payload['dynamic_fields'] ?? []);
            $this->syncServiceItems($serviceOrder, $payload['service_items'] ?? []);

            return $serviceOrder->refresh();
        });
    }

    public function updateServiceOrder(string $id, array $payload): ServiceOrder
    {
        return DB::transaction(function () use ($id, $payload) {
            $serviceOrder = $this->findServiceOrder($id);

            $serviceOrder->update([
                'equipment_type_id' => $payload['equipment_type_id'] ?: null,
                'selected_document_id' => $payload['selected_document_id'] ?: null,
                'equipment_brand' => $payload['equipment_brand'] ?: null,
                'equipment_model' => $payload['equipment_model'] ?: null,
                'equipment_serial_number' => $payload['equipment_serial_number'] ?: null,
            ]);

            $this->syncFieldValues($serviceOrder, $payload['dynamic_fields'] ?? []);
            $this->syncServiceItems($serviceOrder, $payload['service_items'] ?? []);

            return $serviceOrder->refresh();
        });
    }

    public function refreshServiceOrderCustomerSnapshot(string $id): ServiceOrder
    {
        return DB::transaction(function () use ($id) {
            $serviceOrder = ServiceOrder::query()
                ->with('customer')
                ->findOrFail($id);

            if ($serviceOrder->customer) {
                $serviceOrder->update([
                    'customer_snapshot_json' => $this->buildCustomerSnapshot($serviceOrder->customer),
                ]);
            }

            return $serviceOrder->refresh();
        });
    }

    public function duplicateServiceOrder(string $id): ServiceOrder
    {
        return DB::transaction(function () use ($id) {
            $serviceOrder = $this->findServiceOrder($id);

            $clone = ServiceOrder::query()->create([
                'customer_id' => $serviceOrder->customer_id,
                'status_flow_id' => $serviceOrder->status_flow_id,
                'equipment_type_id' => $serviceOrder->equipment_type_id,
                'selected_document_id' => $serviceOrder->selected_document_id,
                'customer_snapshot_json' => $serviceOrder->customer_snapshot_json,
                'equipment_brand' => $serviceOrder->equipment_brand,
                'equipment_model' => $serviceOrder->equipment_model,
                'equipment_serial_number' => $serviceOrder->equipment_serial_number,
                'opened_at' => now(),
                'finished_at' => null,
            ]);

            $fieldRows = $serviceOrder->fieldValues
                ->map(function (ServiceOrderEquipmentFieldValue $fieldValue) use ($clone) {
                    return [
                        'id' => (string) Str::uuid(),
                        'service_order_id' => $clone->id,
                        'equipment_type_field_id' => $fieldValue->equipment_type_field_id,
                        'field_type' => $fieldValue->field_type,
                        'field_label' => $fieldValue->field_label,
                        'field_placeholder' => $fieldValue->field_placeholder,
                        'is_required' => $fieldValue->is_required,
                        'value_text' => $fieldValue->value_text,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all();

            if (! empty($fieldRows)) {
                ServiceOrderEquipmentFieldValue::query()->insert($fieldRows);
            }

            $serviceRows = $serviceOrder->serviceItems
                ->map(function (ServiceOrderServiceItem $item) use ($clone) {
                    return [
                        'id' => (string) Str::uuid(),
                        'service_order_id' => $clone->id,
                        'procedure_id' => $item->procedure_id,
                        'item_name' => $item->item_name,
                        'item_notes' => $item->item_notes,
                        'unit_value' => $item->unit_value,
                        'discount_value' => $item->discount_value,
                        'total_value' => $item->total_value,
                        'sort_order' => $item->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all();

            if (! empty($serviceRows)) {
                ServiceOrderServiceItem::query()->insert($serviceRows);
            }

            return $clone;
        });
    }

    public function deleteServiceOrder(string $id): void
    {
        $serviceOrder = ServiceOrder::query()->findOrFail($id);
        $serviceOrder->delete();
    }

    public function listStatusFlows(): Collection
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        return ServiceOrderStatusFlow::query()
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name']);
    }

    public function listActiveEquipmentTypes(): Collection
    {
        return ServiceOrderEquipmentType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->with([
                'documents:id,equipment_type_id,document_type,title,template_content,path,disk,original_name,variables_json',
                'fields:id,equipment_type_id,field_type,label,placeholder,is_required,default_text',
            ])
            ->get(['id', 'name']);
    }

    public function listProcedures(): Collection
    {
        return ServiceOrderProcedure::query()
            ->orderBy('name')
            ->get(['id', 'name', 'value']);
    }

    public function searchCustomers(string $search = '', int $limit = 15): Collection
    {
        $query = Customer::query()
            ->orderBy('name');

        $term = trim($search);

        if ($term !== '') {
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $query->limit($limit)->get([
            'id',
            'name',
            'cpf_cnpj',
            'cellphone',
            'email',
            'address',
            'number',
            'neighborhood',
            'city',
            'state',
            'status',
            'person',
            'zip_code',
        ]);
    }

    public function workingDays(): array
    {
        return ServiceOrderSetting::singleton()->working_days_json;
    }

    public function holidays(): array
    {
        return ServiceOrderSetting::singleton()->holidays_json ?? [];
    }

    private function buildCustomerSnapshot(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'person' => $customer->person,
            'cpf_cnpj' => $customer->cpf_cnpj,
            'cellphone' => $customer->cellphone,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'zip_code' => $customer->zip_code,
            'address' => $customer->address,
            'number' => $customer->number,
            'neighborhood' => $customer->neighborhood,
            'city' => $customer->city,
            'state' => $customer->state,
        ];
    }

    private function syncFieldValues(ServiceOrder $serviceOrder, array $dynamicFields): void
    {
        ServiceOrderEquipmentFieldValue::query()
            ->where('service_order_id', $serviceOrder->id)
            ->delete();

        $rows = collect($dynamicFields)
            ->filter(fn (array $field) => ! blank($field['field_label'] ?? null))
            ->map(function (array $field) use ($serviceOrder) {
                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $serviceOrder->id,
                    'equipment_type_field_id' => $field['equipment_type_field_id'] ?? null,
                    'field_type' => $field['field_type'] ?? ServiceOrderEquipmentTypeField::TYPE_TEXT,
                    'field_label' => $field['field_label'],
                    'field_placeholder' => $field['field_placeholder'] ?? null,
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'value_text' => blank($field['value_text'] ?? null) ? null : trim((string) $field['value_text']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        if (! empty($rows)) {
            ServiceOrderEquipmentFieldValue::query()->insert($rows);
        }
    }

    private function syncServiceItems(ServiceOrder $serviceOrder, array $serviceItems): void
    {
        ServiceOrderServiceItem::query()
            ->where('service_order_id', $serviceOrder->id)
            ->delete();

        $rows = collect($serviceItems)
            ->map(function (array $serviceItem, int $index) use ($serviceOrder) {
                $unitValue = round((float) ($serviceItem['unit_value'] ?? 0), 2);
                $discountValue = round((float) ($serviceItem['discount_value'] ?? 0), 2);
                $totalValue = max(0, $unitValue - $discountValue);

                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $serviceOrder->id,
                    'procedure_id' => $serviceItem['procedure_id'] ?: null,
                    'item_name' => trim((string) ($serviceItem['item_name'] ?? '')),
                    'item_notes' => blank($serviceItem['item_notes'] ?? null) ? null : trim((string) $serviceItem['item_notes']),
                    'unit_value' => $unitValue,
                    'discount_value' => $discountValue,
                    'total_value' => $totalValue,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter(fn (array $item) => $item['item_name'] !== '')
            ->values()
            ->all();

        if (! empty($rows)) {
            ServiceOrderServiceItem::query()->insert($rows);
        }
    }
}
