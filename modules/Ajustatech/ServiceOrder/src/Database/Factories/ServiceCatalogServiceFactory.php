<?php

namespace Ajustatech\ServiceOrder\Database\Factories;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceCatalogServiceFactory extends Factory
{
    protected $model = ServiceCatalogService::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->randomElement([
                'Troca de tela',
                'Formatação',
                'Limpeza interna',
                'Troca de bateria',
                'Atualização de sistema',
            ]),
            'description' => $this->faker->sentence(),
            'base_price' => $this->faker->randomFloat(2, 50, 600),
            'is_active' => true,
            'is_reusable' => true,
        ];
    }
}
