<?php

namespace Ajustatech\ServiceOrder\Database\Factories\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrderServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderServiceItemFactory extends Factory
{
    protected $model = ServiceOrderServiceItem::class;

    public function definition(): array
    {
        $unitValue = $this->faker->randomFloat(2, 30, 900);
        $discountValue = $this->faker->randomFloat(2, 0, min(80, $unitValue));

        return [
            'service_order_id' => ServiceOrder::factory(),
            'procedure_id' => ServiceOrderProcedure::query()->inRandomOrder()->value('id'),
            'item_name' => $this->faker->randomElement(['Diagnostico inicial', 'Troca de componente', 'Limpeza tecnica']),
            'item_notes' => $this->faker->optional()->sentence(8),
            'unit_value' => $unitValue,
            'discount_value' => $discountValue,
            'total_value' => max(0, $unitValue - $discountValue),
            'sort_order' => 0,
        ];
    }
}
