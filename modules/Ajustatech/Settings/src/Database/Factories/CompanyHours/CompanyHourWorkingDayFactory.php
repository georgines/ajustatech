<?php

namespace Ajustatech\Settings\Database\Factories\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHourWorkingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyHourWorkingDayFactory extends Factory
{
    protected $model = CompanyHourWorkingDay::class;

    public function definition(): array
    {
        $dayKey = $this->faker->randomElement(CompanyHour::DAY_KEYS);

        return [
            'company_hour_id' => CompanyHour::factory(),
            'day_key' => $dayKey,
            'sort_order' => collect(CompanyHour::DAY_KEYS)->search($dayKey) + 1,
        ];
    }
}
