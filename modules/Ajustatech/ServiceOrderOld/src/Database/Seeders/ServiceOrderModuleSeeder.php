<?php

namespace Ajustatech\ServiceOrderOld\Database\Seeders;

use Illuminate\Database\Seeder;

class ServiceOrderModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EquipmentTypesSeeder::class,
            ServiceCatalogSeeder::class,
            AnalysisTypesSeeder::class,
            ServiceOrderDemoSeeder::class,
            AnalysisExecutionDemoSeeder::class,
        ]);
    }
}
