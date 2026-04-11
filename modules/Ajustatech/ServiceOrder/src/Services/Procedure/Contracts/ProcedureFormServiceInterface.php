<?php

namespace Ajustatech\ServiceOrder\Services\Procedure\Contracts;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;

interface ProcedureFormServiceInterface
{
    public function initializeCreateState(ProcedureManagement $component): void;

    public function fillFromProcedure(ProcedureManagement $component, ServiceOrderProcedure $procedure): void;

    public function sanitizeInputs(ProcedureManagement $component): void;

    public function buildPayload(ProcedureManagement $component): array;

    public function validationRules(ProcedureManagement $component): array;

    public function validationAttributes(): array;
}
