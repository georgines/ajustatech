<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevReinstallCommand extends BaseCommand
{
    protected $signature = 'dev:reinstall';
    protected $description = 'Reinstall development database and caches';

    public function handle()
    {
        Artisan::call('db:wipe', [], $this->getOutput());
        Artisan::call('dev:wipe-media', [], $this->getOutput());
        Artisan::call('dev:migrate', [], $this->getOutput());
        Artisan::call('dev:clear', [], $this->getOutput());
        Artisan::call('dev:seed', [], $this->getOutput());

        $this->info('Development environment reinstalled successfully.');
    }
}
