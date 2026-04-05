<?php

namespace Ajustatech\ServiceOrderOld\Providers;

use Ajustatech\ServiceOrderOld\Commands\SeedServiceOrderOldCommand;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceOrders;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceOrderDocuments;
use Ajustatech\ServiceOrderOld\Livewire\ShowServiceCatalog;
use Ajustatech\ServiceOrderOld\Livewire\ShowEquipmentTypes;
use Ajustatech\ServiceOrderOld\Livewire\EditServiceOrderManagement;
use Ajustatech\ServiceOrderOld\Livewire\NewServiceOrderManagement;
use Ajustatech\ServiceOrderOld\Livewire\EquipmentTypeManagement;
use Ajustatech\ServiceOrderOld\Livewire\ServiceCatalogManagement;
use Ajustatech\ServiceOrderOld\Livewire\ShowPendingAnalysisServices;
use Ajustatech\ServiceOrderOld\Livewire\AnalysisExecutionManagement;

class ServiceOrderOldServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__ . '/..';

    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom("$this->path/Database/Migrations");
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", 'service-order');
        $this->loadCommands();
        $this->initializeMenus();
        $this->initializeLivewireComponents();
    }

    private function initializeMenus(): void
    {
        $verticalMenu = json_decode(file_get_contents("$this->path/Menu/verticalMenu.json"));
        $horizontalMenu = json_decode(file_get_contents("$this->path/Menu/horizontalMenu.json"));

        $menu = app(MenuManagerInterface::class);
        $menu->addVerticalMenu($verticalMenu);
        $menu->addHorizontalMenu($horizontalMenu);
    }

    private function initializeLivewireComponents(): void
    {
        Livewire::component('service-order-show-equipment-types', ShowEquipmentTypes::class);
        Livewire::component('service-order-equipment-type-management', EquipmentTypeManagement::class);
        Livewire::component('service-order-show-orders', ShowServiceOrders::class);
        Livewire::component('service-order-order-documents', ShowServiceOrderDocuments::class);
        Livewire::component('service-order-order-edit', EditServiceOrderManagement::class);
        Livewire::component('service-order-new-order-management', NewServiceOrderManagement::class);
        Livewire::component('service-order-show-services', ShowServiceCatalog::class);
        Livewire::component('service-order-service-management', ServiceCatalogManagement::class);
        Livewire::component('service-order-analysis-execution-queue', ShowPendingAnalysisServices::class);
        Livewire::component('service-order-analysis-execution-management', AnalysisExecutionManagement::class);
    }

    private function loadCommands(): void
    {
        $this->commands([
            SeedServiceOrderOldCommand::class,
        ]);
    }
}
