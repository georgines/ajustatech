<?php

namespace Ajustatech\PrintService\Providers;


use Ajustatech\PrintService\Contracts\CellRepositoryInterface;
use Ajustatech\PrintService\Contracts\DocumentsRepositoryInterface;
use Ajustatech\PrintService\Contracts\RowRepositoryInterface;
use Ajustatech\PrintService\Repositories\CellRepository;
use Ajustatech\PrintService\Repositories\DocumentsRepository;
use Ajustatech\PrintService\Repositories\RowRepository;

use Illuminate\Support\ServiceProvider;

class PrintServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CellRepositoryInterface::class, CellRepository::class);
        $this->app->bind(RowRepositoryInterface::class, RowRepository::class);
        $this->app->bind(DocumentsRepositoryInterface::class, DocumentsRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . "/../routes/routes.php");
        $this->loadViewsFrom(__DIR__ . "/../views", 'print-service');
    }
}
