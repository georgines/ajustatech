<?php

namespace Ajustatech\Settings\Providers;

use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Settings\Commands\ServiceOrder\SeedServiceOrderSettingsCommand;
use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Ajustatech\Settings\Livewire\ServiceOrder\ShowServiceOrderSettings;
use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Ajustatech\Settings\Services\ServiceOrder\ServiceOrderSettingsService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SettingsServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__.'/..';

    public function register(): void
    {
        $this->app->bind(
            ServiceOrderSettingsServiceInterface::class,
            ServiceOrderSettingsService::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/service-order.php");
        $this->loadViewsFrom("{$this->path}/Views", 'settings');
        $this->loadMigrationsFrom("{$this->path}/Database/Migrations");
        $this->loadTranslationsFrom("{$this->path}/Lang", 'settings');
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
        Livewire::component('show-service-order-settings', ShowServiceOrderSettings::class);
        Livewire::component('service-order-settings-management', ServiceOrderSettingsManagement::class);
    }

    private function loadCommands(): void
    {
        $this->commands([
            SeedServiceOrderSettingsCommand::class,
        ]);
    }
}

