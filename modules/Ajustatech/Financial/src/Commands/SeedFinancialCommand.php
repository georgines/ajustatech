<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedFinancialCommand extends Command
{

    protected $signature = 'db:seed-financial';

    protected $description = 'Seeds the database with financial data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\Financial\Database\Seeders\FinancialSeeder'
        ]);
    }
}
