<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCashBalances;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyCashBalancesFactory extends Factory
{
    protected $model = CompanyCashBalances::class;

	public function definition(): array
	{
        return [
            'id' => $this->faker->uuid(),
        ];
	}
}
