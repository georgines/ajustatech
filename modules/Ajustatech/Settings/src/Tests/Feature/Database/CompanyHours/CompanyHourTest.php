<?php

namespace Ajustatech\Settings\Tests\Feature\Database\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyHourTest extends TestCase
{
    use RefreshDatabase;

    public function test_singleton_creates_default_working_days_and_no_holidays(): void
    {
        $companyHour = CompanyHour::singleton();

        $this->assertDatabaseHas('company_hours', [
            'id' => $companyHour->id,
        ]);

        $this->assertSame(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], $companyHour->workingDays->pluck('day_key')->all());
        $this->assertCount(0, $companyHour->holidays);
    }

    public function test_can_sync_working_days_and_holidays(): void
    {
        $companyHour = CompanyHour::updateSingleton([
            'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays' => [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Natal', 'date' => '2026-12-25'],
            ],
        ]);

        $this->assertDatabaseHas('company_hours_working_days', [
            'company_hour_id' => $companyHour->id,
            'day_key' => 'monday',
        ]);

        $this->assertDatabaseHas('company_hours_holidays', [
            'company_hour_id' => $companyHour->id,
            'holiday_name' => 'Natal',
            'holiday_date' => '2026-12-25',
        ]);
    }
}
