<?php

namespace Ajustatech\Core\Helpers;

use Illuminate\Support\Facades\Route;
use stdClass;

class MenuRouteResolver implements MenuRouteResolverInterface
{
    public function resolveRoutes(stdClass $menu): stdClass
    {       
        if (isset($menu->menu)) {
            $menu->menu = $this->processMenu($menu->menu);
        }
        return $menu;
    }

    protected function processMenu(array $menu): array
    {
        $queue = $menu;

        foreach ($queue as &$menuItem) {
            $this->processMenuItem($menuItem);
            
            if (isset($menuItem->submenu) && is_array($menuItem->submenu)) {
                foreach ($menuItem->submenu as &$submenuItem) {
                    $this->processMenuItem($submenuItem);
                }
            }
        }

        return $menu;
    }

    protected function processMenuItem(stdClass &$menuItem): void
    {        
        if (isset($menuItem->menuHeader)) {
            return;
        }
        
        if (isset($menuItem->url) && isset($menuItem->slug)) {
            $this->createRelativeUrl($menuItem);
        }
    }

    protected function createRelativeUrl(stdClass &$menuItem): void
    {
        Route::getRoutes()->refreshNameLookups();

        if (Route::has($menuItem->slug)) {
            $menuItem->url = url()->route($menuItem->slug, [], false);

        }
    }
}
