<?php

namespace Ajustatech\ServiceOrder\Database\Seeders\EquipmentType;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeBrandSeeder extends Seeder
{
    public function run(): void
    {
        $types = ServiceOrderEquipmentType::query()->get(['id', 'name']);

        $rows = [];

        foreach ($types as $type) {
            $brandNames = match ($type->name) {
                'Computador desktop' => [
                    ['Dell', 18],
                    ['Lenovo', 14],
                    ['Acer', 9],
                    ['Positivo', 7],
                ],
                'Notebook' => [
                    ['Dell', 22],
                    ['Lenovo', 19],
                    ['Samsung', 11],
                    ['Apple', 8],
                    ['Acer', 6],
                ],
                'Celular' => [
                    ['Samsung', 24],
                    ['Apple', 20],
                    ['Motorola', 17],
                    ['Xiaomi', 12],
                ],
                default => [
                    ['Genérica', 5],
                ],
            };

            foreach ($brandNames as $brandIndex => [$name, $usageCount]) {
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'equipment_type_id' => $type->id,
                    'name' => $name,
                    'usage_count' => $usageCount,
                    'last_used_at' => now()->subDays($brandIndex + 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (! empty($rows)) {
            ServiceOrderEquipmentTypeBrand::query()->insert($rows);
        }
    }
}
