<?php

namespace Ajustatech\ServiceOrder\Providers;

use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\ServiceOrder\Commands\SeedServiceOrderCommand;
use Ajustatech\ServiceOrder\Commands\WipeServiceOrderMediaCommand;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\CreateServiceOrderWizard;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ServiceOrderManagement;
use Ajustatech\ServiceOrder\Livewire\ServiceOrder\ShowServiceOrder;
use Ajustatech\ServiceOrder\Providers\Analysis\AnalysisServiceProvider;
use Ajustatech\ServiceOrder\Providers\EquipmentType\EquipmentTypeServiceProvider;
use Ajustatech\ServiceOrder\Providers\Procedure\ProcedureServiceProvider;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCalendarServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCatalogServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCustomerServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderEquipmentCatalogServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderRecordServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\ServiceOrderCalendarService;
use Ajustatech\ServiceOrder\Services\ServiceOrder\ServiceOrderCatalogService;
use Ajustatech\ServiceOrder\Services\ServiceOrder\ServiceOrderCustomerService;
use Ajustatech\ServiceOrder\Services\ServiceOrder\ServiceOrderEquipmentCatalogService;
use Ajustatech\ServiceOrder\Services\ServiceOrder\ServiceOrderRecordService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ServiceOrderServiceProvider extends ServiceProvider
{
    protected $path = __DIR__ . '/..';

    public function register(): void
    {
        $this->app->register(ProcedureServiceProvider::class);
        $this->app->register(AnalysisServiceProvider::class);
        $this->app->register(EquipmentTypeServiceProvider::class);

        $this->app->bind(ServiceOrderRecordServiceInterface::class, ServiceOrderRecordService::class);
        $this->app->bind(ServiceOrderCatalogServiceInterface::class, ServiceOrderCatalogService::class);
        $this->app->bind(ServiceOrderCustomerServiceInterface::class, ServiceOrderCustomerService::class);
        $this->app->bind(ServiceOrderEquipmentCatalogServiceInterface::class, ServiceOrderEquipmentCatalogService::class);
        $this->app->bind(ServiceOrderCalendarServiceInterface::class, ServiceOrderCalendarService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("$this->path/Routes/web.php");
        $this->loadViewsFrom("$this->path/Views", 'service-order');
        $this->loadMigrationsFrom("$this->path/Database/Migrations/ServiceOrder");
        $this->loadTranslationsFrom("$this->path/Lang", 'service-order');
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
        Livewire::component('show-service-order', ShowServiceOrder::class);
        Livewire::component('service-order-create-wizard', CreateServiceOrderWizard::class);
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
