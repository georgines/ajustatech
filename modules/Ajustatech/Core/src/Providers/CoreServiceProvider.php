<?php

namespace Ajustatech\Core\Providers;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Ajustatech\PrintService\Providers\PrintServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->register(PrintServiceProvider::class);
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
        })->purpose('limpar todos caches');
    }
}
