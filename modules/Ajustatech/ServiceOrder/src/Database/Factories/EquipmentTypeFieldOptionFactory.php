<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\EquipmentTypeFieldOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipmentTypeFieldOptionFactory extends Factory
{
    protected $model = EquipmentTypeFieldOption::class;

    public function definition(): array
    {
        $label = ucfirst($this->faker->unique()->word());

        return [
            'id' => (string) Str::uuid(),
            'equipment_type_field_id' => EquipmentTypeField::factory(),
            'label' => $label,
            'value' => Str::slug($label, '_'),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}

