<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\CompanyCashTransactions;
use Illuminate\Database\Seeder;

class CompanyCashTransactionsSeeder extends Seeder
{
	public function run(): void
	{
		CompanyCashTransactions::factory(10)->create();
	}
}
