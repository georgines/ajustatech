<?php

namespace Ajustatech\Financial\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Financial\Livewire\ShowFinancial;
use Ajustatech\Financial\Livewire\FinancialManagement;
use Ajustatech\Financial\Commands\SeedFinancialCommand;

class FinancialServiceProvider extends ServiceProvider
{

    protected $path = __DIR__ . "/..";

    public function register()
    {

    }

    public function boot()
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", "financial");
        $this->loadMigrationsFrom("$this->path/Database/migrations");
        $this->loadTranslationsFrom("$this->path/Lang", "financial");
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
		Livewire::component('show-financial', ShowFinancial::class);
		Livewire::component('financial-management', FinancialManagement::class);
    }

    private function loadCommands(){
        $this->commands([
            SeedFinancialCommand::class
        ]);
    }
}
