<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedCompanyCashTransactionsCommand extends Command
{

    protected $signature = 'module:seed-company-cash-transactions';

    protected $description = 'Seeds the database with company-cash-transactions data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\Financial\Database\Seeders\CompanyCashTransactionsSeeder'
        ]);
    }
}
