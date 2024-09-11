<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\Financial;
use Illuminate\Database\Seeder;

class FinancialSeeder extends Seeder
{
	public function run(): void
	{
		Financial::factory(10)->create();
	}
}
