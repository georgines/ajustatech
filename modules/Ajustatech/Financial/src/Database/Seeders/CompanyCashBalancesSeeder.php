<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\CompanyCashBalances;
use Illuminate\Database\Seeder;

class CompanyCashBalancesSeeder extends Seeder
{
	public function run(): void
	{
		CompanyCashBalances::factory(10)->create();
	}
}
