<?php

namespace Ajustatech\Settings\Database\Seeders\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Illuminate\Database\Seeder;

class CompanyHoursSeeder extends Seeder
{
    public function run(): void
    {
        $companyHour = CompanyHour::singleton();

        $companyHour->syncWorkingDays(['monday', 'wednesday', 'friday']);
        $companyHour->syncHolidays([
            [
                'name' => 'Confraternizacao Universal',
                'date' => now()->startOfYear()->format('Y-m-d'),
            ],
            [
                'name' => 'Dia do Trabalho',
                'date' => now()->year.'-05-01',
            ],
            [
                'name' => 'Natal',
                'date' => now()->startOfYear()->addMonths(11)->addDays(24)->format('Y-m-d'),
            ],
        ]);
    }
}
