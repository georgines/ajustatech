<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevWipeMediaCommand extends BaseCommand
{
    protected $signature = 'dev:wipe-media';
    protected $description = 'Run module media wipe commands';

    public function handle(): int
    {
        $exitCode = Artisan::call('module:wipe-media', [], $this->getOutput());

        if ($exitCode !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info('Module media wipe completed.');

        return self::SUCCESS;
    }
}
