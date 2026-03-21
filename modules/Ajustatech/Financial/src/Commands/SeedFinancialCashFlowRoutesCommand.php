<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedFinancialCashFlowRoutesCommand extends Command
{
    protected $signature = 'module:seed-financial-cash-flow-routes';

    protected $description = 'Seeds the database with financial cash flow routes data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\FinancialCashFlowRoutesSeeder',
        ]);
    }
}
