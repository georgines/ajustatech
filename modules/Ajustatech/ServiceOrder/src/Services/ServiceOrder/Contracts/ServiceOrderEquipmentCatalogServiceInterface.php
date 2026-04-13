<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts;

interface ServiceOrderEquipmentCatalogServiceInterface
{
    public function listActiveEquipmentTypes(): array;

    public function findEquipmentTypeDetail(string $id): ?array;

    public function findEquipmentTypeDetailOrFail(string $id): array;

    public function resolveBrandId(string $equipmentTypeId, string $brandName): ?string;

    public function searchBrands(string $equipmentTypeId, string $search = ''): array;

    public function rememberBrand(string $equipmentTypeId, string $brandName): array;

    public function selectBrand(string $equipmentTypeId, string $brandId): ?array;

    public function searchModels(string $brandId, string $search = ''): array;

    public function rememberModel(string $brandId, string $modelName): array;

    public function selectModel(string $brandId, string $modelId): ?array;
}
