<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\Financial;
use Illuminate\Database\Eloquent\Factories\Factory;

class FinancialFactory extends Factory
{
    protected $model = Financial::class;

	public function definition(): array
	{
        return [
            'id' => $this->faker->uuid(),
        ];
	}
}
