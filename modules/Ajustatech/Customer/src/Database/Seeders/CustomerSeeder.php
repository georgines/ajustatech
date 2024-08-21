<?php

namespace Ajustatech\Customer\Database\Seeders;

use Ajustatech\Customer\Database\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		Customer::factory(100)->create();
	}
}
