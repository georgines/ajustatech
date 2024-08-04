<?php

namespace Ajustatech\Core\Helpers;

use stdClass;

class MenuManager implements MenuManagerInterface
{
    protected $vertical_menus = [];
    protected $horizontal_menus = [];

    public function addVerticalMenu(stdClass $menu): void
    {
        if (isset($menu->menu)) {
            $this->vertical_menus = array_merge($this->vertical_menus, $menu->menu);
        }
    }

    public function addHorizontalMenu(stdClass $menu): void
    {
        if (isset($menu->menu)) {
            $this->horizontal_menus = array_merge($this->horizontal_menus, $menu->menu);
        }
    }

    public function getVerticalMenus(): stdClass
    {
        $verticalMenu = new stdClass();
        $verticalMenu->menu = $this->vertical_menus;
        return $verticalMenu;
    }

    public function getHorizontalMenus(): stdClass
    {
        $horizontalMenu = new stdClass();
        $horizontalMenu->menu = $this->horizontal_menus;
        return $horizontalMenu;
    }

    public function getMenus(): array
    {
        return [$this->getVerticalMenus(), $this->getHorizontalMenus()];        
    }
}
