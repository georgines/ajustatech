<?php

namespace Ajustatech\ServiceOrder\Database\Seeders;

use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
	public function run(): void
	{
		ServiceOrder::factory(10)->create();
	}
}
