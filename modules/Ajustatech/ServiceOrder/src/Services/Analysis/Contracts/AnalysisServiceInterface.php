<?php

namespace Ajustatech\ServiceOrder\Services\Analysis\Contracts;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Illuminate\Database\Eloquent\Collection;

interface AnalysisServiceInterface
{
    public function listAnalysisServices(): Collection;

    public function findAnalysisService(string $id): ServiceOrderAnalysisService;

    public function createAnalysisService(array $data, array $questions): ServiceOrderAnalysisService;

    public function updateAnalysisService(string $id, array $data, array $questions): ServiceOrderAnalysisService;

    public function deleteAnalysisService(string $id): void;
}
