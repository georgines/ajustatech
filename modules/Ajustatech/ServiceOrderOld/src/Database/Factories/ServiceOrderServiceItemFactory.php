<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceOrderServiceItemFactory extends Factory
{
    protected $model = ServiceOrderServiceItem::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_order_id' => ServiceOrder::factory(),
            'service_catalog_service_id' => ServiceCatalogService::factory(),
            'service_name' => $this->faker->words(2, true),
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->randomFloat(2, 30, 300),
            'discount_amount' => $this->faker->randomFloat(2, 0, 30),
            'service_snapshot' => [
                'id' => null,
                'name' => 'Snapshot',
                'description' => null,
            ],
        ];
    }
}
