<?php

namespace Ajustatech\ServiceOrder\Database\Factories\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    public function definition(): array
    {
        $customer = Customer::query()->inRandomOrder()->first() ?? Customer::factory()->create();
        $equipmentType = ServiceOrderEquipmentType::query()->inRandomOrder()->first() ?? ServiceOrderEquipmentType::factory()->create();
        $defaultStatusId = ServiceOrderStatusFlow::defaultInitialId();

        return [
            'id' => $this->faker->uuid(),
            'status_flow_id' => $defaultStatusId,
            'customer_id' => $customer->id,
            'equipment_type_id' => $equipmentType->id,
            'customer_snapshot_json' => [
                'name' => $customer->name,
                'cpf_cnpj' => $customer->cpf_cnpj,
                'cellphone' => $customer->cellphone,
                'email' => $customer->email,
                'address' => $customer->address,
                'number' => $customer->number,
                'neighborhood' => $customer->neighborhood,
                'city' => $customer->city,
                'state' => $customer->state,
            ],
            'equipment_brand' => $this->faker->randomElement(['Dell', 'Lenovo', 'Samsung', 'Apple']),
            'equipment_model' => strtoupper($this->faker->bothify('??-####')),
            'equipment_serial_number' => strtoupper($this->faker->bothify('SN-########')),
            'opened_at' => now(),
        ];
    }
}
