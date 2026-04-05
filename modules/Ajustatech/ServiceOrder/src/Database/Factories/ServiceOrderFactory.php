<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

	public function definition(): array
	{
        return [
            'id' => $this->faker->uuid(),
        ];
	}
}
