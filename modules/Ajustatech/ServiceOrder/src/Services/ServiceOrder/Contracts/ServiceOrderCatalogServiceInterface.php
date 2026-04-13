<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

interface ServiceOrderCatalogServiceInterface
{
    public function listStatusFlows(): array;

    public function listAnalysisServices(): array;
}
