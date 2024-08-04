<?php
namespace Ajustatech\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Ajustatech\Core\Helpers\MenuManagerInterface;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $menuManager = app(MenuManagerInterface::class);
        $menuData = $menuManager->getMenus();
        View::share('menuData', $menuData);

        Log::info('ViewServiceProvider: menuData');
        Log::debug('menuData:', $menuData);
    }
}
