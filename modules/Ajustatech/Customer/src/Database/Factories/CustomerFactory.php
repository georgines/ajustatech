<?php

namespace Ajustatech\Customer\Database\Factories;

use Ajustatech\Customer\Database\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

	public function definition(): array
	{

		$person  = $this->faker->randomElement(['F', 'J']);

		return [
			'name' => $this->faker->name,
			'person' => $person,
			'cpf_cnpj' => $person === 'F' ?  $this->faker->cpf : $this->faker->cnpj,
			'state_registration' => $this->faker->optional()->text(10),
			'rg' => $this->faker->numerify('#########'),
			'issue_date' => $this->faker->optional()->date,
			'issuer' => $this->faker->optional()->text(20),
			'cellphone' => $this->faker->phoneNumber,
			'phone' => $this->faker->optional()->phoneNumber,
			'email' => $this->faker->unique()->safeEmail,
			'date_of_birth' => $this->faker->date,
			'marital_status' => $this->faker->optional()->randomElement(['Single', 'Married', 'Divorced', 'Widowed']),
			'zip_code' => $this->faker->numerify('#####-###'),
			'address' => $this->faker->address,
			'number' => $this->faker->buildingNumber,
			'neighborhood' => 'barro',
			'city' => $this->faker->city,
			'state' => $this->faker->stateAbbr,
			'birthplace' => $this->faker->optional()->word,
			'credit_limit' => $this->faker->optional()->randomFloat(2, 0, 10000),
			'complement' => $this->faker->optional()->text(20),
			'fathers_name' => $this->faker->optional()->name,
			'mothers_name' => $this->faker->optional()->name,
			'observations' => $this->faker->optional()->text(200),
			'status' => $this->faker->randomElement(['1', '0']),
		];
	}
}
