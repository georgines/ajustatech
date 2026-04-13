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

    public function test_shows_logo_preview_before_saving(): void
    {
        Storage::fake('public');
        config()->set('settings.company.logo.disk', 'public');
        config()->set('settings.company.logo.directory', 'settings/company/logo');

        Livewire::test(CompanySettingsManagement::class)
            ->set('logo', UploadedFile::fake()->image('logo-preview.png', 1080, 1080))
            ->assertSeeText(trans('settings::messages.company_logo_preview_new'))
            ->assertHasNoErrors();
    }

    public function test_validates_required_fields_and_logo_messages_in_portuguese(): void
    {
        Storage::fake('public');
        config()->set('settings.company.logo.disk', 'public');

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', '')
            ->set('cnpj', '')
            ->set('addressLine', '')
            ->set('neighborhood', '')
            ->set('city', '')
            ->set('state', '')
            ->set('phone', '')
            ->set('email', '')
            ->call('save')
            ->assertHasErrors([
                'companyName' => 'required',
                'cnpj' => 'required',
                'addressLine' => 'required',
                'neighborhood' => 'required',
                'city' => 'required',
                'state' => 'required',
                'phone' => 'required',
                'email' => 'required',
            ])
            ->assertSeeText('O campo Nome da empresa é obrigatório.')
            ->assertSeeText('O campo CNPJ é obrigatório.');
    }

    public function test_validates_logo_mime_type_and_dimensions_in_portuguese(): void
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
            ->set('logo', UploadedFile::fake()->create('logo-invalida.txt', 20, 'text/plain'))
            ->call('save')
            ->assertHasErrors(['logo' => 'image'])
            ->assertSeeText('A logo da empresa deve ser uma imagem válida.');

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'CE')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'contato@technova.com.br')
            ->set('logo', UploadedFile::fake()->image('logo-invalida.png', 900, 900))
            ->call('save')
            ->assertHasErrors(['logo' => 'dimensions'])
            ->assertSeeText('A logo da empresa deve ter exatamente 1080x1080 pixels.');
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

    public function test_save_without_new_logo_keeps_current_logo(): void
    {
        $setting = CompanySetting::singleton();
        $existingLogoPath = 'settings/company/logo/'.$setting->id.'/logo-atual.png';
        $expectedLogoUrl = route('settings-company-logo', ['v' => sha1($existingLogoPath)]);

        CompanySetting::updateSingleton([
            'logo_disk' => 'public',
            'logo_path' => $existingLogoPath,
            'logo_original_name' => 'logo-atual.png',
            'logo_mime_type' => 'image/png',
            'logo_size' => 1024,
        ]);

        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', 'TechNova Assistencia')
            ->set('cnpj', '12.345.678/0001-95')
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'CE')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'contato@technova.com.br')
            ->assertSet('currentLogoUrl', $expectedLogoUrl)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settings-saved');

        $updated = CompanySetting::singleton();

        $this->assertSame($existingLogoPath, $updated->logo_path);
        $this->assertSame('logo-atual.png', $updated->logo_original_name);
    }

    public function test_validates_invalid_payload(): void
    {
        Livewire::test(CompanySettingsManagement::class)
            ->set('companyName', '')
            ->set('cnpj', '00.000.000/0000-00')
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'Ceara')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'invalido')
            ->call('save')
            ->assertHasErrors([
                'companyName' => 'required',
                'cnpj',
                'state' => 'size',
                'email' => 'email',
            ])
            ->assertSee('O campo Nome da empresa é obrigatório.');
    }

    public function test_validates_logo_dimensions_as_1080_by_1080(): void
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
            ->set('addressLine', 'Rua das Oficinas, 245')
            ->set('neighborhood', 'Distrito Industrial')
            ->set('city', 'Fortaleza')
            ->set('state', 'CE')
            ->set('phone', '(85) 4000-1234')
            ->set('email', 'contato@technova.com.br')
            ->set('logo', UploadedFile::fake()->image('logo-nova.png', 1080, 1080))
            ->call('save')
            ->assertHasNoErrors();

        $updated = CompanySetting::singleton();

        $this->assertNotSame($oldPath, $updated->logo_path);
        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists((string) $updated->logo_path));
    }
}
