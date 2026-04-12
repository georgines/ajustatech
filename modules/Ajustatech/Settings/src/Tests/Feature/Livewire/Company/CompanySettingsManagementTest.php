<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\Company;

use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Ajustatech\Settings\Livewire\Company\CompanySettingsManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CompanySettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_company_settings_and_upload_logo(): void
    {
        Storage::fake('public');
        config()->set('settings.company.logo.disk', 'public');
        config()->set('settings.company.logo.directory', 'settings/company/logo');

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'CE')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'contato@technova.com.br')
            ->set('logo', UploadedFile::fake()->image('logo-ajustatech.png', 1080, 1080))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settings-saved')
            ->assertSet('companyName', 'TechNova Assistencia');

        $setting = CompanySetting::singleton();

        $this->assertSame('TechNova Assistencia', $setting->company_name);
        $this->assertSame('12345678000195', $setting->cnpj);
        $this->assertNotNull($setting->logo_path);
        $this->assertTrue(Storage::disk('public')->exists((string) $setting->logo_path));
    }

    public function test_save_uses_minimum_queries_without_redirect(): void
    {
        $setting = CompanySetting::singleton();

        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            if (str_contains($query->sql, 'company')) {
                $queries[] = $query->sql;
            }
        });

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'CE')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'contato@technova.com.br')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settings-saved');

        $this->assertLessThanOrEqual(2, count($queries));
        $this->assertDatabaseHas('company', [
            'id' => $setting->id,
            'company_name' => 'TechNova Assistencia',
        ]);
    }

    public function test_validates_invalid_payload(): void
    {
        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', '')
            ->set('cnpj', '00.000.000/0000-00')
            ->set('state', 'Ceara')
            ->set('email', 'invalido')
            ->call('save')
            ->assertHasErrors([
                'companyName' => 'required',
                'cnpj',
                'state' => 'size',
                'email' => 'email',
            ]);
    }

    public function test_validates_logo_dimensions_as_1080_by_1080(): void
    {
        Storage::fake('public');
        config()->set('settings.company.logo.disk', 'public');
        config()->set('settings.company.logo.directory', 'settings/company/logo');

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('logo', UploadedFile::fake()->image('logo-invalida.png', 900, 900))
            ->call('save')
            ->assertHasErrors(['logo' => 'dimensions']);
    }

    public function test_replaces_old_logo_and_deletes_previous_file(): void
    {
        Storage::fake('public');
        config()->set('settings.company.logo.disk', 'public');
        config()->set('settings.company.logo.directory', 'settings/company/logo');

        $setting = CompanySetting::singleton();
        $oldPath = 'settings/company/logo/'.$setting->id.'/logo-antiga.png';
        Storage::disk('public')->put($oldPath, 'old-logo-content');

        CompanySetting::updateSingleton([
            'logo_disk' => 'public',
            'logo_path' => $oldPath,
            'logo_original_name' => 'logo-antiga.png',
            'logo_mime_type' => 'image/png',
            'logo_size' => 1000,
        ]);

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('logo', UploadedFile::fake()->image('logo-nova.png', 1080, 1080))
            ->call('save')
            ->assertHasNoErrors();

        $updated = CompanySetting::singleton();

        $this->assertNotSame($oldPath, $updated->logo_path);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists((string) $updated->logo_path));
    }
}
