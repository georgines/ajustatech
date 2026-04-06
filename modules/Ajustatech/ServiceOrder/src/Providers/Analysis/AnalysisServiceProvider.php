<?php

namespace Ajustatech\ServiceOrder\Providers\Analysis;

use Ajustatech\ServiceOrder\Commands\Analysis\SeedAnalysisCommand;
use Ajustatech\ServiceOrder\Commands\Analysis\WipeAnalysisMediaCommand;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Ajustatech\ServiceOrder\Livewire\Analysis\ShowAnalysisServices;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AnalysisServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__ . '/../..';

    public function register(): void
    {
        config()->set('media_wipe.modules.service-order.analysis.directories', [
            'service-order/analysis',
        ]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom("{$this->path}/Routes/analysis.php");
        $this->loadMigrationsFrom("{$this->path}/Database/Migrations/Analysis");

        $this->commands([
            SeedAnalysisCommand::class,
            WipeAnalysisMediaCommand::class,
        ]);

        Livewire::component('show-analysis-services', ShowAnalysisServices::class);
        Livewire::component('analysis-management', AnalysisManagement::class);
    }
}
