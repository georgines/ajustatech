<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Ajustatech\Core\Commands\MakeModuleCommand;
use Ajustatech\Core\Commands\MakeLivewireComponentCommand;
use Ajustatech\Core\Commands\MakeModuleMenuCommand;
use Ajustatech\Core\Commands\MakeModuleModelCommand;
use Ajustatech\Core\Commands\MakeModuleRoutesCommand;
use Ajustatech\Core\Commands\MakeModuleProviderCommand;

class CommandServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->commands([
            MakeModuleCommand::class,
            MakeLivewireComponentCommand::class,
            MakeModuleMenuCommand::class,
            MakeModuleModelCommand::class,
            MakeModuleRoutesCommand::class,
            MakeModuleProviderCommand::class,
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