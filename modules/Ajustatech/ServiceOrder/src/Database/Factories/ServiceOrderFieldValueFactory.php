<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\EquipmentTypeField;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrderFieldValue;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderFieldValueFactory extends Factory
{
    protected $model = ServiceOrderFieldValue::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_order_id' => ServiceOrder::factory(),
            'equipment_type_field_id' => EquipmentTypeField::factory(),
            'field_slug' => $this->faker->unique()->slug('_'),
            'field_type' => EquipmentFieldType::TEXT,
            'value_text' => $this->faker->sentence(),
            'value_json' => null,
            'field_snapshot' => [
                'name' => 'Observacoes',
                'slug' => 'observacoes',
                'field_type' => EquipmentFieldType::TEXT,
            ],
        ];
    }
}

