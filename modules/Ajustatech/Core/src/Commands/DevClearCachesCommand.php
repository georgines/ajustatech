<?php

namespace Ajustatech\Core\Commands;

use Illuminate\Support\Facades\Artisan;

class DevClearCachesCommand extends BaseCommand
{
    protected $signature = 'dev:clear';
    protected $description = 'Limpar todos caches';

    public function handle()
    {

        Artisan::call("clear-compiled", [], $this->getOutput());
        Artisan::call("cache:clear", [], $this->getOutput());
        Artisan::call("config:clear", [], $this->getOutput());
        Artisan::call("queue:clear", [], $this->getOutput());
        Artisan::call("schedule:clear-cache", [], $this->getOutput());
        Artisan::call("view:clear", [], $this->getOutput());

        $this->info("🔥 all application caches have been cleared");
    }
}
