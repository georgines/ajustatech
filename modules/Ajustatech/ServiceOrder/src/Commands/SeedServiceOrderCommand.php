<?php

namespace Ajustatech\ServiceOrder\Commands;

use Illuminate\Console\Command;

class SeedServiceOrderCommand extends Command
{

    protected $signature = 'module:seed-service-order';

    protected $description = 'Seeds the database with service-order data';

    public function handle()
    {
        $this->call('db:seed', [
            '--class'=>'Ajustatech\ServiceOrder\Database\Seeders\ServiceOrderSeeder'
        ]);
    }
}
