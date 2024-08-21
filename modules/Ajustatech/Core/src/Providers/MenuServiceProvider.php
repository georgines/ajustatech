<?php

namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Core\Helpers\MenuManager;
use Illuminate\Support\Facades\Log;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuManagerInterface::class, MenuManager::class);
    }

    public function boot(): void
    {
        // $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        // $verticalMenuData = json_decode($verticalMenuJson);
        // $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
        // $horizontalMenuData = json_decode($horizontalMenuJson);

        // $menuManager = app(MenuManagerInterface::class);
        // $menuManager->addVerticalMenu($verticalMenuData);
        // $menuManager->addHorizontalMenu($horizontalMenuData);

        // Log::info('MenuServiceProvider: menuData');
        // Log::debug('menuData:', $menuManager->getMenus());
    }
}
