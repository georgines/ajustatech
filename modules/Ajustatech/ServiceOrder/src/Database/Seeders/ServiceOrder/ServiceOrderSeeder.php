<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
	public function run(): void
	{
		ServiceOrder::factory(10)->create();
	}
}
