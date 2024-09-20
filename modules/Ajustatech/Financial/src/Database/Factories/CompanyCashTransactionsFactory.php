<?php

namespace Ajustatech\Financial\Database\Factories;

use Ajustatech\Financial\Database\Models\CompanyCashTransactions;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyCashTransactionsFactory extends Factory
{
    protected $model = CompanyCashTransactions::class;

	public function definition(): array
	{
        return [
            'id' => $this->faker->uuid(),
        ];
	}
}
