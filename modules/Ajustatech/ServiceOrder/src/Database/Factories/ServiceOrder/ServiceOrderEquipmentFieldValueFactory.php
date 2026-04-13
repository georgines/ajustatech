<?php

namespace Ajustatech\ServiceOrder\Database\Factories\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderEquipmentFieldValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderEquipmentFieldValueFactory extends Factory
{
    protected $model = ServiceOrderEquipmentFieldValue::class;

    public function definition(): array
    {
        return [
            'service_order_id' => ServiceOrder::factory(),
            'equipment_type_field_id' => null,
            'field_type' => 'text',
            'field_label' => $this->faker->randomElement(['Defeito relatado', 'Senha do equipamento', 'Observacao tecnica']),
            'field_placeholder' => 'Preencha o valor',
            'is_required' => false,
            'value_text' => $this->faker->sentence(6),
        ];
    }
}
