<?php

namespace Ajustatech\ServiceOrderOld\Commands;

use Illuminate\Console\Command;

class SeedServiceOrderOldCommand extends Command
{
    protected $signature = 'module:seed-service-order-old';
    protected $description = 'Seed Service Order Old module development data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrderOld\\Database\\Seeders\\ServiceOrderModuleSeeder',
        ]);
    }
}
