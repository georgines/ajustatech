<?php

namespace Ajustatech\ServiceOrder\Commands\EquipmentType;

use Illuminate\Console\Command;

class SeedEquipmentTypeCommand extends Command
{
    protected $signature = 'feature:seed-service-order-equipment-type';

    protected $description = 'Seed Equipment Type feature data for Service Order module';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\EquipmentType\\ServiceOrderEquipmentTypeSeeder',
        ]);

        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\EquipmentType\\ServiceOrderEquipmentTypeBrandSeeder',
        ]);

        $this->call('db:seed', [
            '--class' => 'Ajustatech\\ServiceOrder\\Database\\Seeders\\EquipmentType\\ServiceOrderEquipmentTypeModelSeeder',
        ]);
    }
}
