<?php

namespace Ajustatech\ServiceOrder\Services\Procedure\Contracts;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Eloquent\Collection;

interface ProcedureServiceInterface
{
    public function listProcedures(): Collection;

    public function findProcedure(string $id): ServiceOrderProcedure;

    public function createProcedure(array $data, array $media = []): ServiceOrderProcedure;

    public function updateProcedure(string $id, array $data, array $media = [], array $deleteMediaIds = []): ServiceOrderProcedure;

    public function deleteProcedure(string $id): void;
}
