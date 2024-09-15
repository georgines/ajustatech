<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevSeedCommand extends BaseCommand
{
    protected $signature = 'dev:seed';
    protected $description = 'Seed modules';

    public function handle()
    {

            Artisan::call('module:seed', [], $this->getOutput());

        $this->info("");
    }
}
