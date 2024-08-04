<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ajustatech\NewModule\Providers\NewModuleServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    public function register(): void
    { 
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);
        $this->app->register(NewModuleServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);
    }

    public function boot(): void
    {        
    }
}
