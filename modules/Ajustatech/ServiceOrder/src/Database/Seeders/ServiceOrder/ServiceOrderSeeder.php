<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderEquipmentFieldValue;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderServiceItem;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $customers = Customer::query()->limit(10)->get();

        if ($customers->isEmpty()) {
            $customers = Customer::factory(10)->create();
        }

        $equipmentTypes = ServiceOrderEquipmentType::query()->with(['fields', 'documents'])->limit(5)->get();

        if ($equipmentTypes->isEmpty()) {
            $equipmentTypes = ServiceOrderEquipmentType::factory(3)->create()->load(['fields', 'documents']);
        }

        $orders = ServiceOrder::factory(18)->make()->map(function (ServiceOrder $serviceOrder) use ($customers, $equipmentTypes) {
            $customer = $customers->random();
            $equipmentType = $equipmentTypes->random();
            $document = $equipmentType->documents->first();

            $serviceOrder->customer_id = $customer->id;
            $serviceOrder->equipment_type_id = $equipmentType->id;
            $serviceOrder->selected_document_id = $document?->id;
            $serviceOrder->customer_snapshot_json = [
                'id' => $customer->id,
                'name' => $customer->name,
                'cpf_cnpj' => $customer->cpf_cnpj,
                'cellphone' => $customer->cellphone,
                'email' => $customer->email,
                'address' => $customer->address,
                'number' => $customer->number,
                'neighborhood' => $customer->neighborhood,
                'city' => $customer->city,
                'state' => $customer->state,
            ];

            return $serviceOrder;
        });

        $orders->each->save();

        foreach ($orders as $order) {
            ServiceOrderServiceItem::factory(random_int(1, 3))->create([
                'service_order_id' => $order->id,
            ]);

            $fields = $equipmentTypes->firstWhere('id', $order->equipment_type_id)?->fields ?? collect();

            foreach ($fields as $field) {
                ServiceOrderEquipmentFieldValue::factory()->create([
                    'service_order_id' => $order->id,
                    'equipment_type_field_id' => $field->id,
                    'field_type' => $field->field_type,
                    'field_label' => $field->label,
                    'field_placeholder' => $field->placeholder,
                    'is_required' => $field->is_required,
                    'value_text' => $field->default_text,
                ]);
            }
        }
    }
}
