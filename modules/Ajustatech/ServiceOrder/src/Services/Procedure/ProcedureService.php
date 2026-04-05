<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class ProcedureService implements ProcedureServiceInterface
{
    public function listProcedures(): Collection
    {
        return ServiceOrderProcedure::query()
            ->latest()
            ->get();
    }

    public function findProcedure(string $id): ServiceOrderProcedure
    {
        return ServiceOrderProcedure::query()->findOrFail($id);
    }

    public function createProcedure(array $data): ServiceOrderProcedure
    {
        return ServiceOrderProcedure::query()->create($data);
    }

    public function updateProcedure(string $id, array $data): ServiceOrderProcedure
    {
        $procedure = $this->findProcedure($id);
        $procedure->update($data);

        return $procedure->fresh();
    }

    public function deleteProcedure(string $id): void
    {
        ServiceOrderProcedure::query()
            ->whereKey($id)
            ->delete();
    }
}
