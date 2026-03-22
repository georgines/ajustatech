<?php

namespace Ajustatech\ServiceOrder\Database\Seeders;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Diagnostico tecnico', 'description' => 'Analise completa do equipamento.', 'base_price' => 80, 'is_active' => true, 'is_reusable' => true],
            ['name' => 'Formatacao e backup', 'description' => 'Formatacao do sistema com backup basico.', 'base_price' => 180, 'is_active' => true, 'is_reusable' => true],
            ['name' => 'Troca de tela', 'description' => 'Substituicao de display danificado.', 'base_price' => 350, 'is_active' => true, 'is_reusable' => true],
            ['name' => 'Limpeza interna', 'description' => 'Limpeza fisica e troca de pasta termica.', 'base_price' => 150, 'is_active' => true, 'is_reusable' => true],
        ];

        foreach ($defaults as $item) {
            ServiceCatalogService::query()->updateOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
