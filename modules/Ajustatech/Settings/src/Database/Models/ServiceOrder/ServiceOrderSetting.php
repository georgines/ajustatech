<?php

namespace Ajustatech\Settings\Database\Models\ServiceOrder;

use Ajustatech\Settings\Database\Factories\ServiceOrder\ServiceOrderSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderSetting extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_settings';

    protected $fillable = [
        'initial_order_number',
    ];

    protected $casts = [
        'initial_order_number' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderSettingFactory::new();
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

        $setting->update([
            'initial_order_number' => max(1, (int) ($attributes['initial_order_number'] ?? 1)),
        ]);

        return $setting->refresh();
    }

    public static function defaultAttributes(): array
    {
        return [
            'initial_order_number' => 1000,
        ];
    }
}
