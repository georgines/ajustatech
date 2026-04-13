<?php

namespace Ajustatech\Settings\Database\Factories\Company;

use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanySettingFactory extends Factory
{
    protected $model = CompanySetting::class;

    public function definition(): array
    {
        return [
            'company_name' => 'TechNova Assistencia',
            'cnpj' => '12345678000195',
            'address_line' => $this->faker->streetAddress(),
            'neighborhood' => 'Centro',
            'city' => $this->faker->city(),
            'state' => 'CE',
            'phone' => '(85) 4000-1234',
            'email' => 'contato@technova.com.br',
            'logo_disk' => null,
            'logo_path' => null,
            'logo_original_name' => null,
            'logo_mime_type' => null,
            'logo_size' => null,
        ];
    }
}
