<?php

namespace Ajustatech\ServiceOrderOld\Database\Seeders;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderFieldValue;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderServiceItem;
use Illuminate\Database\Seeder;

class ServiceOrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $equipmentTypes = EquipmentType::query()->where('is_active', true)->get();
        $services = ServiceCatalogService::query()->where('is_active', true)->get();

        if ($equipmentTypes->isEmpty() || $services->isEmpty()) {
            return;
        }

        $statuses = ['open', 'completed', 'canceled', 'open'];

        for ($i = 1; $i <= 4; $i++) {
            $equipmentType = $equipmentTypes->random();
            $order = ServiceOrder::factory()->create([
                'equipment_type_id' => $equipmentType->id,
                'equipment_name' => $equipmentType->name,
                'status' => $statuses[$i - 1] ?? 'open',
                'equipment_type_snapshot' => [
                    'id' => $equipmentType->id,
                    'name' => $equipmentType->name,
                    'description' => $equipmentType->description,
                    'image_path' => $equipmentType->image_path,
                    'image_disk' => $equipmentType->image_disk,
                ],
                'fields_snapshot' => [
                    [
                        'id' => 'demo-document-field',
                        'name' => 'Termo de entrada',
                        'slug' => 'termo_entrada',
                        'field_type' => 'document',
                        'sort_order' => 1,
                        'is_printable' => true,
                    ],
                ],
            ]);

            $service = $services->random();
            $quantity = random_int(1, 2);
            $unitPrice = (float) $service->base_price;
            $gross = $quantity * $unitPrice;
            $discount = round(min($gross, random_int(0, 30) / 10), 2);

            ServiceOrderServiceItem::factory()->create([
                'service_order_id' => $order->id,
                'service_catalog_service_id' => $service->id,
                'service_name' => $service->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'service_snapshot' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'base_price' => $unitPrice,
                    'discount' => $discount,
                    'steps' => [],
                ],
            ]);

            ServiceOrderFieldValue::factory()->create([
                'service_order_id' => $order->id,
                'field_slug' => 'termo_entrada',
                'field_type' => 'document',
                'value_text' => 'Termo de entrada preenchido para a OS demo ' . $i,
                'field_snapshot' => [
                    'name' => 'Termo de entrada',
                    'slug' => 'termo_entrada',
                    'field_type' => 'document',
                ],
            ]);
        }
    }
}
