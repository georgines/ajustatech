<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedCompanyCashCommand extends Command
{

    protected $signature = 'module:seed-company-cash';

    protected $description = 'Seeds the database with company-cash data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\Financial\Database\Seeders\CompanyCashSeeder'
        ]);
    }
}
