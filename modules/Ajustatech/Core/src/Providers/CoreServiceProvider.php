<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ajustatech\Customer\Providers\CustomerServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected $path = __DIR__ . "/..";

    public function register(): void
    {
        $this->app->register(CommandServiceProvider::class);
        $this->app->register(MenuServiceProvider::class);
        $this->app->register(CustomerServiceProvider::class);
        $this->app->register(ViewServiceProvider::class);        
    }

    public function boot(): void
    {
        $this->loadViewsFrom("$this->path/Views", "core");
        
    }
}
