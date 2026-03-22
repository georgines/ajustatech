<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EquipmentTypeFactory extends Factory
{
    protected $model = EquipmentType::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->randomElement(['Notebook', 'Desktop', 'Celular', 'Tablet']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}

