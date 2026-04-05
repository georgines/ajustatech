<?php

namespace Ajustatech\ServiceOrder\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\ServiceOrder\Commands\SeedServiceOrderCommand;
use Ajustatech\ServiceOrder\Commands\WipeServiceOrderMediaCommand;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ShowServiceOrder;
use Ajustatech\ServiceOrder\Providers\Procedure\ProcedureServiceProvider;

class ServiceOrderServiceProvider extends ServiceProvider
{

    protected $path = __DIR__ . "/..";

    public function register(): void
    {
        $this->app->register(ProcedureServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", "service-order");
        $this->loadMigrationsFrom("$this->path/Database/Migrations/ServiceOrder");
        $this->loadTranslationsFrom("$this->path/Lang", "service-order");
        $this->loadCommands();
        $this->initializeMenus();
        $this->initializeLivewireComponents();
    }

    private function initializeMenus()
    {
        $verticalMenu = json_decode(file_get_contents("$this->path/Menu/verticalMenu.json"));
        $horizontalMenu = json_decode(file_get_contents("$this->path/Menu/horizontalMenu.json"));

        $menu = app(MenuManagerInterface::class);
        $menu->addVerticalMenu($verticalMenu);
        $menu->addHorizontalMenu($horizontalMenu);
    }

	private function initializeLivewireComponents()
    {
		Livewire::component('show-service-order', ShowServiceOrder::class);
		Livewire::component('service-order-management', ServiceOrderManagement::class);
    }

    private function loadCommands(): void
    {
        $this->commands([
            SeedServiceOrderCommand::class,
            WipeServiceOrderMediaCommand::class,
        ]);
    }
}
