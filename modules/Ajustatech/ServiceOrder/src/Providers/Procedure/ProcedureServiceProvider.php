<?php

namespace Ajustatech\ServiceOrder\Providers\Procedure;

use Ajustatech\ServiceOrder\Commands\Procedure\SeedProcedureCommand;
use Ajustatech\ServiceOrder\Commands\Procedure\WipeProcedureMediaCommand;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Livewire\Procedure\ShowProcedures;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Ajustatech\ServiceOrder\Services\Procedure\ProcedureService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ProcedureServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__ . '/../..';

    public function register(): void
    {
        $this->app->bind(
            ProcedureServiceInterface::class,
            ProcedureService::class
        );

        $this->registerMediaDirectories();
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/procedure.php");
        $this->loadMigrationsFrom("{$this->path}/Database/Migrations/Procedure");

        $this->commands([
            SeedProcedureCommand::class,
            WipeProcedureMediaCommand::class,
        ]);

        Livewire::component('show-procedures', ShowProcedures::class);
        Livewire::component('procedure-management', ProcedureManagement::class);
    }

    private function registerMediaDirectories(): void
    {
        config()->set('media_wipe.modules.service-order.procedure.directories', [
            'service-order/procedures',
        ]);
    }
}
