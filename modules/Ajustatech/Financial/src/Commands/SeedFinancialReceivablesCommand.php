<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedFinancialReceivablesCommand extends Command
{
    protected $signature = 'module:seed-financial-receivables';

    protected $description = 'Seeds the database with financial receivables data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\FinancialReceivablesSeeder',
        ]);
    }
}
