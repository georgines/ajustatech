<?php

namespace Ajustatech\ServiceOrder\Providers\Analysis;

use Ajustatech\ServiceOrder\Commands\Analysis\SeedAnalysisCommand;
use Ajustatech\ServiceOrder\Commands\Analysis\WipeAnalysisMediaCommand;
use Ajustatech\ServiceOrder\Livewire\Analysis\AnalysisManagement;
use Ajustatech\ServiceOrder\Livewire\Analysis\ShowAnalysisServices;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisManagementService;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisQuestionDraftService;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisQuestionMapperService;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisQuestionPayloadService;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisQuestionSanitizerService;
use Ajustatech\ServiceOrder\Services\Analysis\AnalysisService;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisManagementServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionDraftServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionMapperServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionPayloadServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisQuestionSanitizerServiceInterface;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisServiceInterface;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AnalysisServiceProvider extends ServiceProvider
{
    protected string $path = __DIR__.'/../..';

    public function register(): void
    {
        $this->app->bind(AnalysisServiceInterface::class, AnalysisService::class);
        $this->app->bind(AnalysisManagementServiceInterface::class, AnalysisManagementService::class);
        $this->app->bind(AnalysisQuestionSanitizerServiceInterface::class, AnalysisQuestionSanitizerService::class);
        $this->app->bind(AnalysisQuestionPayloadServiceInterface::class, AnalysisQuestionPayloadService::class);
        $this->app->bind(AnalysisQuestionDraftServiceInterface::class, AnalysisQuestionDraftService::class);
        $this->app->bind(AnalysisQuestionMapperServiceInterface::class, AnalysisQuestionMapperService::class);

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
