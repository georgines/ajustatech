<?php

namespace Ajustatech\Settings\Database\Factories\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderStatusFlowFactory extends Factory
{
    protected $model = ServiceOrderStatusFlow::class;

    public function definition(): array
    {
        return [
            'code' => 'status_' . $this->faker->unique()->numberBetween(1, 9999),
            'name' => 'Status ' . $this->faker->unique()->numberBetween(1, 9999),
            'sort_order' => $this->faker->numberBetween(1, 99),
            'is_terminal' => false,
            'is_default_initial' => false,
        ];
    }

    public function entrada(): self
    {
        return $this->state(fn () => [
            'code' => 'entrada',
            'name' => 'Entrada',
            'sort_order' => 1,
            'is_terminal' => false,
            'is_default_initial' => true,
        ]);
    }
}


