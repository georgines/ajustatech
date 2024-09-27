<?php

namespace Ajustatech\Financial\Providers;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use Ajustatech\Core\Helpers\MenuManagerInterface;
use Ajustatech\Financial\Livewire\ShowCompanyCash;
use Ajustatech\Financial\Livewire\CompanyCashManagement;
use Ajustatech\Financial\Commands\SeedCompanyCashCommand;
use Ajustatech\Financial\Commands\SeedCompanyCashBalancesCommand;
use Ajustatech\Financial\Commands\SeedCompanyCashTransactionsCommand;
use Ajustatech\Financial\Livewire\CompanyCashTransactionsManagement;
use Ajustatech\Financial\Livewire\ShowCompanyCashTransactions;
use Ajustatech\Financial\Services\CompanyCashServiceInterface;
use Ajustatech\Financial\Services\CompanyCashService;
use Ajustatech\Financial\Services\CompanyCashTransactionsService;
use Ajustatech\Financial\Services\CompanyCashTransactionsServiceInterface;

class FinancialServiceProvider extends ServiceProvider
{

    protected $path = __DIR__ . "/..";

    public function register()
    {
        $this->app->bind(CompanyCashServiceInterface::class, CompanyCashService::class);
        $this->app->bind(CompanyCashTransactionsServiceInterface::class, CompanyCashTransactionsService::class);
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
        Livewire::component('show-company-cash', ShowCompanyCash::class);
        Livewire::component('company-cash-management', CompanyCashManagement::class);
        Livewire::component('show-company-cash-transactions', ShowCompanyCashTransactions::class);
        Livewire::component('company-cash-transactions-management', CompanyCashTransactionsManagement::class);
    }

    private function loadCommands()
    {
        $this->commands([
            SeedCompanyCashTransactionsCommand::class,
            SeedCompanyCashBalancesCommand::class,
            SeedCompanyCashCommand::class,
        ]);
    }
}
