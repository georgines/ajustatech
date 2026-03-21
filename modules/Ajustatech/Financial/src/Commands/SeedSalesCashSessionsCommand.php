<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedSalesCashSessionsCommand extends Command
{
    protected $signature = 'module:seed-sales-cash-sessions';

    protected $description = 'Seeds the database with sales cash sessions data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\SalesCashSessionsSeeder',
        ]);
    }
}
