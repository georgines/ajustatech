<?php

namespace Ajustatech\ServiceOrder\Database\Seeders;

use Illuminate\Database\Seeder;

class ServiceOrderModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EquipmentTypesSeeder::class,
            ServiceCatalogSeeder::class,
            ServiceOrderDemoSeeder::class,
        ]);
    }
}

