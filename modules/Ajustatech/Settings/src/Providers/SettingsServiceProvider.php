<?php

namespace Ajustatech\Settings\Providers;

use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Settings\Commands\ServiceOrder\SeedServiceOrderSettingsCommand;
use Ajustatech\Settings\Livewire\Company\CompanySettingsManagement;
use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Ajustatech\Settings\Providers\CompanyHours\CompanyHoursServiceProvider;
use Ajustatech\Settings\Services\Company\CompanySettingsService;
use Ajustatech\Settings\Services\Company\Contracts\CompanySettingsServiceInterface;
use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Ajustatech\Settings\Services\ServiceOrder\ServiceOrderSettingsService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SettingsServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__.'/..';

    public function register(): void
    {
        $this->mergeConfigFrom("{$this->path}/config/settings.php", 'settings');

        $this->app->register(CompanyHoursServiceProvider::class);

        $this->app->bind(
            ServiceOrderSettingsServiceInterface::class,
            ServiceOrderSettingsService::class
        );

        $this->app->bind(
            CompanySettingsServiceInterface::class,
            CompanySettingsService::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/service-order.php");
        $this->loadRoutesFrom("{$this->path}/Routes/company.php");
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
        Livewire::component('service-order-settings-management', ServiceOrderSettingsManagement::class);
        Livewire::component('company-settings-management', CompanySettingsManagement::class);
    }

    private function loadCommands(): void
    {
        $this->commands([
            SeedServiceOrderSettingsCommand::class,
        ]);
    }
}
