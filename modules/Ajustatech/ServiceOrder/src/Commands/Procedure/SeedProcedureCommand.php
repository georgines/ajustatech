<?php

namespace Ajustatech\ServiceOrder\Commands\Procedure;

use Illuminate\Console\Command;

class SeedProcedureCommand extends Command
{
    protected $signature = 'feature:seed-service-order-procedure';
    protected $description = 'Seed Procedure feature data for Service Order module';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\Procedure\\ServiceOrderProcedureSeeder',
        ]);
    }
}
