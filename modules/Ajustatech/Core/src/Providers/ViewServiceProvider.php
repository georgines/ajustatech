<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Core\Helpers\MenuRouteResolverInterface;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $menuManager = app(MenuManagerInterface::class);

        $verticalMenuRouterResolver = app(MenuRouteResolverInterface::class);
        $horizontalMenuRouterResolver = app(MenuRouteResolverInterface::class);

        $verticalMenu = $verticalMenuRouterResolver->resolveRoutes($menuManager->getVerticalMenus());
        $horizontalMenu = $horizontalMenuRouterResolver->resolveRoutes($menuManager->getHorizontalMenus());
        $menuData = [$verticalMenu, $horizontalMenu];

        View::share('menuData', $menuData);

        Log::info('ViewServiceProvider: menuData');
        Log::debug('menuData:', $menuData);
    }
}
