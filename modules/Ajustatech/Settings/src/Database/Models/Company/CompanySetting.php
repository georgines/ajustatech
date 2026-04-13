<?php

namespace Ajustatech\Settings\Database\Models\Company;

use Ajustatech\Settings\Database\Factories\Company\CompanySettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'company';

    protected $fillable = [
        'company_name',
        'cnpj',
        'address_line',
        'neighborhood',
        'city',
        'state',
        'phone',
        'email',
        'logo_disk',
        'logo_path',
        'logo_original_name',
        'logo_mime_type',
        'logo_size',
    ];

    protected $casts = [
        'logo_size' => 'integer',
    ];

    protected static function newFactory()
    {
        return CompanySettingFactory::new();
    }

    public static function singleton(): self
    {
        $first = static::query()->first();

        if ($first !== null) {
            return $first;
        }

        return static::query()->create(static::defaultAttributes());
    }

    public static function updateSingleton(array $attributes): self
    {
        $setting = static::singleton();
        $setting->update($attributes);

        return $setting->refresh();
    }

    public static function defaultAttributes(): array
    {
        return [
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
        ];
    }

    public function hasLogo(): bool
    {
        return filled($this->logo_disk) && filled($this->logo_path);
    }
}
