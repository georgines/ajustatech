<?php

namespace Ajustatech\ServiceOrder\Database\Factories\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentTypeBrandFactory extends Factory
{
    protected $model = ServiceOrderEquipmentTypeBrand::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'equipment_type_id' => ServiceOrderEquipmentType::factory(),
            'name' => $this->faker->randomElement([
                'Dell',
                'Lenovo',
                'Samsung',
                'Apple',
                'Motorola',
            ]),
            'usage_count' => $this->faker->numberBetween(0, 35),
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
