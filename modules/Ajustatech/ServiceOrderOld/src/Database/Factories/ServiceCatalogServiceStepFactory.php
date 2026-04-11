<?php

namespace Ajustatech\ServiceOrderOld\Database\Factories;

use Ajustatech\ServiceOrderOld\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrderOld\Database\Models\ServiceCatalogServiceStep;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceCatalogServiceStepFactory extends Factory
{
    protected $model = ServiceCatalogServiceStep::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_catalog_service_id' => ServiceCatalogService::factory(),
            'name' => $this->faker->sentence(3),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_required' => $this->faker->boolean(70),
            'help_text' => $this->faker->sentence(),
            'technician_report_label' => 'Relato tecnico',
            'requires_image_proof' => $this->faker->boolean(50),
            'is_active' => true,
        ];
    }
}

