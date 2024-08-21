<?php

namespace Ajustatech\Core\Helpers;

use stdClass;

interface MenuManagerInterface
{
    public function addVerticalMenu(stdClass $menu): void;
    public function addHorizontalMenu(stdClass $menu): void;
    public function getVerticalMenus(): stdClass;
    public function getHorizontalMenus(): stdClass;
    public function getMenus(): array;
}
