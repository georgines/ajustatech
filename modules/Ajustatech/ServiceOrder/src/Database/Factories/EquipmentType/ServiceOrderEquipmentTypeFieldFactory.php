<?php

namespace Ajustatech\ServiceOrder\Database\Factories\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeField;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentTypeFieldFactory extends Factory
{
    protected $model = ServiceOrderEquipmentTypeField::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'equipment_type_id' => ServiceOrderEquipmentType::factory(),
            'field_type' => ServiceOrderEquipmentTypeField::TYPE_TEXT,
            'label' => $this->faker->randomElement([
                'Numero de serie',
                'Senha de desbloqueio',
                'Observacoes tecnicas',
            ]),
            'placeholder' => $this->faker->optional()->sentence(4),
            'default_text' => $this->faker->optional()->sentence(6),
            'is_required' => $this->faker->boolean(60),
            'disk' => null,
            'path' => null,
            'original_name' => null,
            'mime_type' => null,
            'extension' => null,
            'size' => null,
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    public function imageField(): self
    {
        return $this->state(fn () => [
            'field_type' => ServiceOrderEquipmentTypeField::TYPE_IMAGE,
            'placeholder' => null,
            'default_text' => null,
        ]);
    }
}
