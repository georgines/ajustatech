<?php

namespace Ajustatech\Settings\Database\Seeders\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Database\Seeder;

class ServiceOrderSettingsSeeder extends Seeder
{
    public function run(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        ServiceOrderSetting::updateSingleton([
            'initial_order_number' => 1000,
            'working_days_json' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays_json' => [
                now()->startOfYear()->format('Y-m-d'),
                now()->startOfYear()->addMonths(11)->addDays(24)->format('Y-m-d'),
            ],
        ]);
    }
}


