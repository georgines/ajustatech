<?php

namespace Ajustatech\Settings\Tests\Feature\Database\Company;

use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_setting_singleton_creates_default_row(): void
    {
        $setting = CompanySetting::singleton();

        $this->assertDatabaseHas('company_settings', [
            'id' => $setting->id,
            'company_name' => 'TechNova Assistencia',
            'cnpj' => '12345678000195',
        ]);
    }

    public function test_can_update_company_setting_singleton(): void
    {
        $updated = CompanySetting::updateSingleton([
            'company_name' => 'Ajustatech Centro',
            'city' => 'Aurora',
            'state' => 'CE',
        ]);

        $this->assertDatabaseHas('company_settings', [
            'id' => $updated->id,
            'company_name' => 'Ajustatech Centro',
            'city' => 'Aurora',
            'state' => 'CE',
        ]);
    }
}
