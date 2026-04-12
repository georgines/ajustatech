<?php

namespace Ajustatech\Settings\Database\Seeders\Company;

use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Illuminate\Database\Seeder;

class CompanySettingsSeeder extends Seeder
{
    public function run(): void
    {
        CompanySetting::updateSingleton([
            'company_name' => 'TechNova Assistencia',
            'cnpj' => '12345678000195',
            'address_line' => 'Rua das Oficinas, 245',
            'neighborhood' => 'Distrito Industrial',
            'city' => 'Fortaleza',
            'state' => 'CE',
            'phone' => '(85) 4000-1234',
            'email' => 'contato@technova.com.br',
            'logo_disk' => null,
            'logo_path' => null,
            'logo_original_name' => null,
            'logo_mime_type' => null,
            'logo_size' => null,
        ]);
    }
}
