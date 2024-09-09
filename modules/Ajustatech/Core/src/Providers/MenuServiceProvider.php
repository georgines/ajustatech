<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Core\Helpers\MenuManager;
use Ajustatech\Core\Helpers\MenuRouteResolverInterface;
use Ajustatech\Core\Helpers\MenuRouteResolver;


class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuManagerInterface::class, MenuManager::class);
        $this->app->bind(MenuRouteResolverInterface::class, MenuRouteResolver::class);        
    }

    public function boot(): void
    {
    }
}
