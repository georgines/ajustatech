<?php

namespace Ajustatech\Core\Tests\Unit;

use Ajustatech\Core\Helpers\MenuManager;
use PHPUnit\Framework\TestCase;
use stdClass;

class MenuManagerTest extends TestCase
{
    protected MenuManager $menuManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuManager = new MenuManager();
    }

    public function test_add_vertical_menu()
    {
        $menu = new stdClass();
        $menu->menu = [
            [
                "name" => "Home",
                "icon" => "icon-home",
                "slug" => "home",
                "url" => "/"
            ]
        ];

        $this->menuManager->addVerticalMenu($menu);
        $this->assertEquals($menu, $this->menuManager->getVerticalMenus());
    }

    public function test_add_horizontal_menu()
    {
        $menu = new stdClass();
        $menu->menu = [
            [
                "name" => "Finance",
                "icon" => "icon-finance",
                "slug" => "finance",
                "submenu" => [
                    [
                        "url" => "invoices",
                        "name" => "Invoices",
                        "slug" => "finance-invoices"
                    ]
                ]
            ]
        ];

        $this->menuManager->addHorizontalMenu($menu);
        $this->assertEquals($menu, $this->menuManager->getHorizontalMenus());
    }

    public function test_add_multiple_vertical_menus()
    {
        $menu1 = new stdClass();
        $menu1->menu = [
            [
                "name" => "Settings",
                "icon" => "icon-settings",
                "slug" => "settings",
                "url" => "/settings"
            ]
        ];

        $menu2 = new stdClass();
        $menu2->menu = [
            [
                "name" => "Sales",
                "icon" => "icon-sales",
                "slug" => "sales",
                "submenu" => [
                    [
                        "url" => "orders",
                        "name" => "Orders",
                        "slug" => "sales-orders"
                    ]
                ]
            ]
        ];
       
        $this->menuManager->addVerticalMenu($menu1);      
        $this->menuManager->addVerticalMenu($menu2);
        
        $expectedMenus = new stdClass();
        $expectedMenus->menu = array_merge($menu1->menu, $menu2->menu);
        $this->assertEquals($expectedMenus, $this->menuManager->getVerticalMenus());
    }

    public function test_get_menus_initially_returns_empty_array()
    {
        $expectedVerticalMenu = new stdClass();
        $expectedVerticalMenu->menu = [];

        $expectedHorizontalMenu = new stdClass();
        $expectedHorizontalMenu->menu = [];

        $this->assertEquals([$expectedVerticalMenu, $expectedHorizontalMenu], $this->menuManager->getMenus());
    }
}
