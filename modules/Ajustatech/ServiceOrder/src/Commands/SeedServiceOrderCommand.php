<?php

namespace Ajustatech\ServiceOrder\Commands;

use Illuminate\Console\Command;

class SeedServiceOrderCommand extends Command
{

    protected $signature = 'module:seed-service-order';

    protected $description = 'Seed Service Order module data and run feature seed commands';

    public function handle(): int
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\ServiceOrder\\ServiceOrderSeeder',
        ]);

        $this->info('Running command: feature:seed-service-order-procedure');
        $exitCode = $this->call('feature:seed-service-order-procedure');

        if ($exitCode !== self::SUCCESS) {
            $this->error('Command failed: feature:seed-service-order-procedure');

            return self::FAILURE;
        }

        $this->info('Running command: feature:seed-service-order-analysis');
        $exitCode = $this->call('feature:seed-service-order-analysis');

        if ($exitCode !== self::SUCCESS) {
            $this->error('Command failed: feature:seed-service-order-analysis');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
