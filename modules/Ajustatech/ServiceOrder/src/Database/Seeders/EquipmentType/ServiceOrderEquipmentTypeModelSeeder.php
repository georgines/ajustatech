<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeModelSeeder extends Seeder
{
    public function run(): void
    {
        $types = ServiceOrderEquipmentType::query()->with('brands:id,equipment_type_id,name')->get(['id', 'name']);

        $rows = [];

        foreach ($types as $type) {
            $modelNamesByBrand = match ($type->name) {
                'Computador desktop' => [
                    'Dell' => [
                        ['OptiPlex 7090', 18],
                        ['Inspiron 3910', 11],
                    ],
                    'Lenovo' => [
                        ['ThinkCentre M70q', 15],
                        ['ThinkCentre M75s', 9],
                    ],
                    'Acer' => [
                        ['Aspire TC-1760', 8],
                    ],
                    'Positivo' => [
                        ['Master D6200', 5],
                    ],
                ],
                'Notebook' => [
                    'Dell' => [
                        ['Inspiron 15', 24],
                        ['Vostro 3400', 12],
                    ],
                    'Lenovo' => [
                        ['ThinkPad E14', 21],
                        ['IdeaPad 3', 10],
                    ],
                    'Samsung' => [
                        ['Galaxy Book 2', 10],
                    ],
                    'Apple' => [
                        ['MacBook Air M2', 7],
                    ],
                ],
                'Celular' => [
                    'Samsung' => [
                        ['Galaxy S23', 23],
                        ['Galaxy A54', 15],
                    ],
                    'Apple' => [
                        ['iPhone 13', 18],
                        ['iPhone 14', 9],
                    ],
                    'Motorola' => [
                        ['Moto G54', 16],
                        ['Edge 40', 8],
                    ],
                    'Xiaomi' => [
                        ['Redmi Note 13', 12],
                    ],
                ],
                default => [],
            };

            foreach ($modelNamesByBrand as $brandName => $modelNames) {
                $brand = $type->brands->firstWhere('name', $brandName);

                if (! $brand instanceof ServiceOrderEquipmentTypeBrand) {
                    continue;
                }

                foreach ($modelNames as $modelIndex => [$name, $usageCount]) {
                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'equipment_type_brand_id' => $brand->id,
                        'name' => $name,
                        'usage_count' => $usageCount,
                        'last_used_at' => now()->subDays($modelIndex + 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($rows)) {
            ServiceOrderEquipmentTypeModel::query()->insert($rows);
        }
    }
}
