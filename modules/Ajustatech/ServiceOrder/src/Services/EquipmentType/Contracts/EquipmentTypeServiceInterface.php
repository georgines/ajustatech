<?php

namespace Ajustatech\ServiceOrder\Services\EquipmentType\Contracts;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Illuminate\Support\Collection;

interface EquipmentTypeServiceInterface
{
    public function listEquipmentTypes(string $search = '', string $status = 'all', int $limitPerPage = 10): Collection;

    public function findEquipmentType(string $id): ServiceOrderEquipmentType;

    public function createEquipmentType(array $data, array $documents, array $fields): ServiceOrderEquipmentType;

    public function updateEquipmentType(string $id, array $data, array $documents, array $fields, array $filesToDelete): ServiceOrderEquipmentType;

    public function deleteEquipmentType(string $id): void;

    public function toggleEquipmentTypeStatus(string $id): void;

    public function duplicateEquipmentType(string $id): ServiceOrderEquipmentType;
}
