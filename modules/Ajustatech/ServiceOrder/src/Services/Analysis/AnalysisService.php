<?php

namespace Ajustatech\ServiceOrder\Services\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Ajustatech\ServiceOrder\Services\Analysis\Contracts\AnalysisServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class AnalysisService implements AnalysisServiceInterface
{
    public function listAnalysisServices(): Collection
    {
        return ServiceOrderAnalysisService::listForIndex();
    }

    public function findAnalysisService(string $id): ServiceOrderAnalysisService
    {
        return ServiceOrderAnalysisService::findWithQuestionsOrFail($id);
    }

    public function createAnalysisService(array $data, array $questions): ServiceOrderAnalysisService
    {
        return ServiceOrderAnalysisService::createWithQuestions($data, $questions);
    }

    public function updateAnalysisService(string $id, array $data, array $questions): ServiceOrderAnalysisService
    {
        return ServiceOrderAnalysisService::updateWithQuestionsById($id, $data, $questions);
    }

    public function deleteAnalysisService(string $id): void
    {
        ServiceOrderAnalysisService::deleteById($id);
    }
}
