<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class ProcedureService implements ProcedureServiceInterface
{
    public function listProcedures(): Collection
    {
        return ServiceOrderProcedure::listWithMedia();
    }

    public function findProcedure(string $id): ServiceOrderProcedure
    {
        return ServiceOrderProcedure::findWithMediaOrFail($id);
    }

    public function createProcedure(array $data, array $media = []): ServiceOrderProcedure
    {
        return ServiceOrderProcedure::createWithMedia($data, $media);
    }

    public function updateProcedure(string $id, array $data, array $media = [], array $deleteMediaIds = []): ServiceOrderProcedure
    {
        $procedure = ServiceOrderProcedure::findOrFailById($id);

        return $procedure->updateWithMedia($data, $media, $deleteMediaIds);
    }

    public function deleteProcedure(string $id): void
    {
        $procedure = ServiceOrderProcedure::findWithMediaOrFail($id);
        $procedure->deleteWithMedia();
    }
}
