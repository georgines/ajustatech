<?php

namespace Ajustatech\Core\Providers;

use Ajustatech\Core\Commands\MakeModuleCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
// use Ajustatech\PrintService\Providers\PrintServiceProvider;
use Ajustatech\Core\Commands\MakeLivewireComponentCommand;

class CoreServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        // $this->app->register(PrintServiceProvider::class);
        $this->commands([
            MakeModuleCommand::class,
            MakeLivewireComponentCommand::class
        ]);
    }

    public function boot(): void
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
