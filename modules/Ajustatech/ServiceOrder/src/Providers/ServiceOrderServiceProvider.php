<?php

namespace Ajustatech\ServiceOrder\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrder;
use Ajustatech\ServiceOrder\Livewire\ServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Commands\SeedServiceOrderCommand;
use Ajustatech\ServiceOrder\Services\Procedure\ProcedureService;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;

class ServiceOrderServiceProvider extends ServiceProvider
{

    protected $path = __DIR__ . "/..";

    public function register()
    {
        $this->app->bind(
            ProcedureServiceInterface::class,
            ProcedureService::class
        );
    }

    public function boot()
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", "service-order");
        $this->loadMigrationsFrom($this->migrationPaths());
        $this->loadTranslationsFrom("$this->path/Lang", "service-order");
        $this->loadCommands();
        $this->initializeMenus();
        $this->initializeLivewireComponents();
    }

    private function migrationPaths(): array
    {
        $basePath = "$this->path/Database/Migrations";

        if (!is_dir($basePath)) {
            return [];
        }

        $subDirectories = glob($basePath . '/*', GLOB_ONLYDIR) ?: [];

        return array_values(array_unique([
            $basePath,
            ...$subDirectories,
        ]));
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
		Livewire::component('show-procedures', ShowProcedures::class);
		Livewire::component('procedure-management', ProcedureManagement::class);
    }

    private function loadCommands(){
        $this->commands([
            SeedServiceOrderCommand::class
        ]);
    }
}
