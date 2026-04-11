<?php

namespace Ajustatech\ServiceOrder\Commands;

use Illuminate\Console\Command;

class WipeServiceOrderMediaCommand extends Command
{
    protected $signature = 'module:wipe-media-service-order';
    protected $description = 'Run Service Order feature media wipe commands';

    public function handle(): int
    {
        $this->info('Running command: feature:wipe-media-service-order-procedure');
        $exitCode = $this->call('feature:wipe-media-service-order-procedure');

        if ($exitCode !== self::SUCCESS) {
            $this->error('Command failed: feature:wipe-media-service-order-procedure');

            return self::FAILURE;
        }

        $this->info('Running command: feature:wipe-media-service-order-analysis');
        $exitCode = $this->call('feature:wipe-media-service-order-analysis');

        if ($exitCode !== self::SUCCESS) {
            $this->error('Command failed: feature:wipe-media-service-order-analysis');

            return self::FAILURE;
        }

        $this->info('Running command: feature:wipe-media-service-order-equipment-type');
        $exitCode = $this->call('feature:wipe-media-service-order-equipment-type');

        if ($exitCode !== self::SUCCESS) {
            $this->error('Command failed: feature:wipe-media-service-order-equipment-type');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
