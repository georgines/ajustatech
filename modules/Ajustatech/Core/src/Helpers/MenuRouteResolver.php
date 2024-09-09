<?php

namespace Ajustatech\Core\Helpers;

use Illuminate\Support\Facades\Route;
use stdClass;

class MenuRouteResolver implements MenuRouteResolverInterface
{
    public function resolveRoutes(stdClass $menu): stdClass
    {
        // Verifica se o JSON contém o campo 'menu' e o processa
        if (isset($menu->menu)) {
            $menu->menu = $this->processMenu($menu->menu);
        }

        // Retorna o objeto JSON com o menu processado
        return $menu;
    }

    protected function processMenu(array $menu): array
    {
        $queue = $menu;  // Usamos uma fila para processar itens de menu e submenus iterativamente

        foreach ($queue as &$menuItem) {
            $this->processMenuItem($menuItem);

            // Se o item tiver submenu, adicionamos os submenus à fila para processar posteriormente
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
        // Verifica se é um menuHeader (não precisa de alteração)
        if (isset($menuItem->menuHeader)) {
            return;
        }

        // Verifica se o item tem URL e slug para criar uma URL relativa
        if (isset($menuItem->url) && isset($menuItem->slug)) {
            $this->createRelativeUrl($menuItem);
        }
    }

    protected function createRelativeUrl(stdClass &$menuItem): void
    {
        Route::getRoutes()->refreshNameLookups();
                
        if (Route::has($menuItem->slug)) {
            $menuItem->url = url()->route($menuItem->slug, [], false); // Passando o slug corretamente

        }
    }
}
