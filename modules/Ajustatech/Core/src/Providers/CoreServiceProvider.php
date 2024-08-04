<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    public function register(): void
    { 
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);       
        $this->app->register(ViewServiceProvider::class);
    }

    public function boot(): void
    {        
    }
}
