<?php

namespace Ajustatech\ServiceOrder\Database\Factories\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentTypeModelFactory extends Factory
{
    protected $model = ServiceOrderEquipmentTypeModel::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'equipment_type_brand_id' => ServiceOrderEquipmentTypeBrand::factory()->for(ServiceOrderEquipmentType::factory(), 'equipmentType'),
            'name' => $this->faker->randomElement([
                'Inspiron 15',
                'ThinkPad E14',
                'Galaxy S23',
                'iPhone 13',
                'Moto G54',
            ]),
            'usage_count' => $this->faker->numberBetween(0, 35),
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
