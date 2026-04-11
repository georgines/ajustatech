<?php

namespace Ajustatech\ServiceOrder\Commands\Analysis;

use Illuminate\Console\Command;

class SeedAnalysisCommand extends Command
{
    protected $signature = 'feature:seed-service-order-analysis';
    protected $description = 'Seed Analysis feature data for Service Order module';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\Analysis\\ServiceOrderAnalysisSeeder',
        ]);
    }
}

