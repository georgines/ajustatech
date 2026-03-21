<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedFinancialPayablesCommand extends Command
{
    protected $signature = 'module:seed-financial-payables';

    protected $description = 'Seeds the database with financial payables data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\FinancialPayablesSeeder',
        ]);
    }
}
