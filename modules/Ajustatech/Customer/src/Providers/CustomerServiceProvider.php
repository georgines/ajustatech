<?php

namespace Ajustatech\Customer\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Customer\Livewire\ShowCustomer;
use Ajustatech\Customer\Livewire\CustomerManagement;


class CustomerServiceProvider extends ServiceProvider
{
    protected $path = __DIR__ . "/..";

    public function register()
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/views", "customer");
        $this->loadMigrationsFrom("$this->path/Database/migrations");
    }

    public function boot()
    {
        $this->initializeMenus();
        $this->initializeLivewireComponents();
    }

    public function initializeMenus()
    {
        $verticalMenu = json_decode(file_get_contents("$this->path/menu/verticalMenu.json"));
        $horizontalMenu = json_decode(file_get_contents("$this->path/menu/verticalMenu.json"));

        $menu = app(MenuManagerInterface::class);
        $menu->addVerticalMenu($verticalMenu);
        $menu->addHorizontalMenu($horizontalMenu);
    }

    public function initializeLivewireComponents()
    {
        Livewire::component('show-customer', ShowCustomer::class);
        Livewire::component('customer-management', CustomerManagement::class);
    }
}
