<?php

namespace Ajustatech\Core\Helpers;

use Illuminate\Support\Facades\Route;
use stdClass;

class MenuRouteResolver implements MenuRouteResolverInterface
{
    public function resolveRoutes(stdClass $menu): stdClass
    {
        if ($this->hasMenu($menu)) {
            $menu->menu = array_map([$this, 'resolveMenuItem'], $menu->menu);
        } else {
            $menu = $this->resolveUrlForMenuItem($menu);
        }

        return $menu;
    }

    private function resolveMenuItem($item): stdClass
    {
        if ($this->hasSubmenu($item)) {
            $item->submenu = array_map([$this, 'resolveMenuItem'], $item->submenu); // Resolve submenus recursivamente
        } else {
            $item = $this->resolveUrlForMenuItem($item);
        }

        return $item;
    }

    private function resolveUrlForMenuItem(stdClass $menuItem): stdClass
    {
        if ($this->hasSlug($menuItem)) {
            if ($this->routeExists($menuItem->slug)) {
                $menuItem->url = url()->route($menuItem->slug, [], false); // Gera URL relativa com base no slug
            }
        }

        return $menuItem;
    }

    private function hasMenu(stdClass $menu): bool
    {
        return isset($menu->menu) && is_array($menu->menu);
    }

    private function hasSubmenu($item): bool
    {
        return isset($item->submenu) && is_array($item->submenu);
    }

    private function hasSlug(stdClass $menuItem): bool
    {
        return isset($menuItem->slug);
    }

    private function routeExists(string $slug): bool
    {
        return Route::has($slug);
    }
}
