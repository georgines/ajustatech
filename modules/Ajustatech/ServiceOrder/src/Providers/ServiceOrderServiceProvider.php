<?php

namespace Ajustatech\ServiceOrder\Providers;

use Ajustatech\ServiceOrder\Commands\SeedServiceOrderCommand;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrders;
use Ajustatech\ServiceOrder\Livewire\ShowServiceOrderDocuments;
use Ajustatech\ServiceOrder\Livewire\ShowServiceCatalog;
use Ajustatech\ServiceOrder\Livewire\ShowEquipmentTypes;
use Ajustatech\ServiceOrder\Livewire\EditServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\NewServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\EquipmentTypeManagement;
use Ajustatech\ServiceOrder\Livewire\ServiceCatalogManagement;

class ServiceOrderServiceProvider extends ServiceProvider
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
    }

    private function loadCommands(): void
    {
        $this->commands([
            SeedServiceOrderCommand::class,
        ]);
    }
}
