<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedFinancialCardBrandCommand extends Command
{
    protected $signature = 'module:seed-financial-card-brands';

    protected $description = 'Seeds the database with financial card brands data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\FinancialCardBrandSeeder',
        ]);
    }
}