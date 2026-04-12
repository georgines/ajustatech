<?php

namespace Ajustatech\Settings\Providers\CompanyHours;

use Ajustatech\Settings\Commands\CompanyHours\SeedCompanyHoursCommand;
use Ajustatech\Settings\Livewire\CompanyHours\CompanyHoursSettingsManagement;
use Ajustatech\Settings\Services\CompanyHours\CompanyHoursSettingsService;
use Ajustatech\Settings\Services\CompanyHours\Contracts\CompanyHoursSettingsServiceInterface;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CompanyHoursServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__.'/../..';

    public function register(): void
    {
        $this->app->bind(
            CompanyHoursSettingsServiceInterface::class,
            CompanyHoursSettingsService::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/company-hours.php");
        $this->commands([
            SeedCompanyHoursCommand::class,
        ]);

        Livewire::component('company-hours-settings-management', CompanyHoursSettingsManagement::class);
    }
}
