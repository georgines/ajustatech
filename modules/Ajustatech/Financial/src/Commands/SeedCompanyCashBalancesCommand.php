<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedCompanyCashBalancesCommand extends Command
{

    protected $signature = 'module:seed-company-cash-balances';

    protected $description = 'Seeds the database with company-cash-balances data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\Financial\Database\Seeders\CompanyCashBalancesSeeder'
        ]);
    }
}
