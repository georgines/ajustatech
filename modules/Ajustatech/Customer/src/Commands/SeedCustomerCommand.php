<?php

namespace Ajustatech\Customer\Commands;

use Illuminate\Console\Command;

class SeedCustomerCommand extends Command
{

    protected $signature = 'db:seed-customer';

    protected $description = 'Seeds the database with customer data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\Customer\Database\Seeders\CustomerSeeder'
        ]);
    }
}
