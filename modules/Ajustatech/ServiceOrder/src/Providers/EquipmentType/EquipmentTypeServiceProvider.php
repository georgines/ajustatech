<?php

namespace Ajustatech\ServiceOrder\Providers\EquipmentType;

use Ajustatech\ServiceOrder\Commands\EquipmentType\SeedEquipmentTypeCommand;
use Ajustatech\ServiceOrder\Commands\EquipmentType\WipeEquipmentTypeMediaCommand;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\EquipmentTypeManagement;
use Ajustatech\ServiceOrder\Livewire\EquipmentType\ShowEquipmentTypes;
use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeManagementServiceInterface;
use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeServiceInterface;
use Ajustatech\ServiceOrder\Services\EquipmentType\EquipmentTypeManagementService;
use Ajustatech\ServiceOrder\Services\EquipmentType\EquipmentTypeService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class EquipmentTypeServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__.'/../..';

    public function register(): void
    {
        $this->app->bind(EquipmentTypeServiceInterface::class, EquipmentTypeService::class);
        $this->app->bind(EquipmentTypeManagementServiceInterface::class, EquipmentTypeManagementService::class);

        config()->set('media_wipe.modules.service-order.equipment-type.directories', [
            'service-order/equipment-types',
        ]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/equipment-type.php");
        $this->loadMigrationsFrom("{$this->path}/Database/Migrations/EquipmentType");

        $this->commands([
            SeedEquipmentTypeCommand::class,
            WipeEquipmentTypeMediaCommand::class,
        ]);

        Livewire::component('show-equipment-types', ShowEquipmentTypes::class);
        Livewire::component('equipment-type-management', EquipmentTypeManagement::class);
    }
}
