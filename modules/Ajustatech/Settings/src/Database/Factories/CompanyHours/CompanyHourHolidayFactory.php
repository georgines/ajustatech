<?php

namespace Ajustatech\Settings\Database\Factories\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHourHoliday;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyHourHolidayFactory extends Factory
{
    protected $model = CompanyHourHoliday::class;

    public function definition(): array
    {
        return [
            'company_hour_id' => CompanyHour::factory(),
            'holiday_name' => 'Confraternizacao Universal',
            'holiday_date' => now()->startOfYear()->format('Y-m-d'),
        ];
    }
}
