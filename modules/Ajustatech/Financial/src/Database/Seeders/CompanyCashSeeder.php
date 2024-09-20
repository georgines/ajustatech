<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Database\Seeder;

class CompanyCashSeeder extends Seeder
{
	public function run(): void
	{
		CompanyCash::factory(10)->create();
	}
}
