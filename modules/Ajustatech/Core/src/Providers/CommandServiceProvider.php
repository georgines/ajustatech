<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Ajustatech\Core\Commands\MakeModuleCommand;
use Ajustatech\Core\Commands\MakeLivewireComponentCommand;

class CommandServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->commands([
            MakeModuleCommand::class,
            MakeLivewireComponentCommand::class
        ]);

    }

    public function boot():void
    {
        Artisan::command('dev',  function () {
            Artisan::call("clear-compiled ");
            Artisan::call("cache:clear  ");
            Artisan::call("config:clear");
            Artisan::call("queue:clear");
            Artisan::call("schedule:clear-cache");
            Artisan::call("view:clear");
            $this->info("all application caches have been cleared");
        })->purpose('limpar todos caches');
    }
}