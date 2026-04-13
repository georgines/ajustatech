<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCatalogServiceInterface;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;

class ServiceOrderCatalogService implements ServiceOrderCatalogServiceInterface
{
    public function listStatusFlows(): array
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        return ServiceOrderStatusFlow::listForSettings()
            ->map(fn ($statusFlow) => [
                'id' => (string) $statusFlow->id,
                'code' => (string) $statusFlow->code,
                'name' => (string) $statusFlow->name,
            ])
            ->all();
    }

    public function listAnalysisServices(): array
    {
        return ServiceOrderAnalysisService::listForIndex()
            ->map(fn (ServiceOrderAnalysisService $analysisService) => $analysisService->toServiceOrderOption())
            ->all();
    }
}
