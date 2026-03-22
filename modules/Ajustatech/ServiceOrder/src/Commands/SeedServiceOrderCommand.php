<?php

namespace Ajustatech\ServiceOrder\Commands;

use Illuminate\Console\Command;

class SeedServiceOrderCommand extends Command
{
    protected $signature = 'module:seed-service-order';
    protected $description = 'Seed Service Order module development data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\ServiceOrderModuleSeeder',
        ]);
    }
}

