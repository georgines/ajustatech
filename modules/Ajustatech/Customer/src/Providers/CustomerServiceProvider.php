<?php

namespace Ajustatech\Customer\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Customer\Livewire\ShowCustomer;
use Ajustatech\Customer\Livewire\CustomerManagement;
use Ajustatech\Customer\Commands\SeedCustomerCommand;

class CustomerServiceProvider extends ServiceProvider
{
    protected $path = __DIR__ . "/..";

    public function register()
    {

    }

    public function boot()
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", "customer");
        $this->loadMigrationsFrom("$this->path/Database/migrations");
        $this->loadTranslationsFrom("$this->path/Lang", "customer");
        $this->loadCommands();
        $this->initializeMenus();
        $this->initializeLivewireComponents();
    }

    private function initializeMenus()
    {
        $verticalMenu = json_decode(file_get_contents("$this->path/Menu/verticalMenu.json"));
        $horizontalMenu = json_decode(file_get_contents("$this->path/Menu/verticalMenu.json"));

        $menu = app(MenuManagerInterface::class);
        $menu->addVerticalMenu($verticalMenu);
        $menu->addHorizontalMenu($horizontalMenu);
    }

    private function initializeLivewireComponents()
    {
        Livewire::component('show-customer', ShowCustomer::class);
        Livewire::component('customer-management', CustomerManagement::class);
    }

    private function loadCommands(){
        $this->commands([
            SeedCustomerCommand::class
        ]);
    }
}
