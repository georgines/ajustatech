<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyCashFactory extends Factory
{
    protected $model = CompanyCash::class;

	public function definition(): array
	{
        return [
            'id' => $this->faker->uuid(),
        ];
	}
}
