<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeModel;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderEquipmentCatalogServiceInterface;

class ServiceOrderEquipmentCatalogService implements ServiceOrderEquipmentCatalogServiceInterface
{
    public function listActiveEquipmentTypes(): array
    {
        return ServiceOrderEquipmentType::listActiveOptions()
            ->map(fn (ServiceOrderEquipmentType $equipmentType) => $equipmentType->toServiceOrderOption())
            ->all();
    }

    public function findEquipmentTypeDetail(string $id): ?array
    {
        $equipmentType = ServiceOrderEquipmentType::findActiveWithDetails($id);

        return $equipmentType?->toServiceOrderDetail();
    }

    public function findEquipmentTypeDetailOrFail(string $id): array
    {
        return ServiceOrderEquipmentType::findActiveWithDetailsOrFail($id)->toServiceOrderDetail();
    }

    public function resolveBrandId(string $equipmentTypeId, string $brandName): ?string
    {
        return ServiceOrderEquipmentTypeBrand::resolveIdForEquipmentTypeAndName($equipmentTypeId, $brandName);
    }

    public function searchBrands(string $equipmentTypeId, string $search = ''): array
    {
        return ServiceOrderEquipmentTypeBrand::listForEquipmentType($equipmentTypeId, $search)
            ->map(fn (ServiceOrderEquipmentTypeBrand $brand) => $brand->toServiceOrderOption())
            ->all();
    }

    public function rememberBrand(string $equipmentTypeId, string $brandName): array
    {
        return ServiceOrderEquipmentTypeBrand::recordUsage($equipmentTypeId, $brandName)->toServiceOrderOption();
    }

    public function selectBrand(string $equipmentTypeId, string $brandId): ?array
    {
        return ServiceOrderEquipmentTypeBrand::findForEquipmentType($equipmentTypeId, $brandId)?->toServiceOrderOption();
    }

    public function searchModels(string $brandId, string $search = ''): array
    {
        return ServiceOrderEquipmentTypeModel::listForBrand($brandId, $search)
            ->map(fn (ServiceOrderEquipmentTypeModel $model) => $model->toServiceOrderOption())
            ->all();
    }

    public function rememberModel(string $brandId, string $modelName): array
    {
        return ServiceOrderEquipmentTypeModel::recordUsage($brandId, $modelName)->toServiceOrderOption();
    }

    public function selectModel(string $brandId, string $modelId): ?array
    {
        return ServiceOrderEquipmentTypeModel::findForBrand($brandId, $modelId)?->toServiceOrderOption();
    }
}
