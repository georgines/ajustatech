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

    public const DAY_KEYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    protected $table = 'service_order_settings';

    protected $fillable = [
        'initial_order_number',
        'working_days_json',
        'holidays_json',
    ];

    protected $casts = [
        'initial_order_number' => 'integer',
        'working_days_json' => 'array',
        'holidays_json' => 'array',
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
            'working_days_json' => static::normalizeWorkingDays($attributes['working_days_json'] ?? []),
            'holidays_json' => static::normalizeHolidays($attributes['holidays_json'] ?? []),
        ]);

        return $setting->refresh();
    }

    public static function defaultAttributes(): array
    {
        return [
            'initial_order_number' => 1000,
            'working_days_json' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays_json' => [],
        ];
    }

    public static function normalizeWorkingDays(array $days): array
    {
        $normalized = collect($days)
            ->map(fn ($day) => strtolower(trim((string) $day)))
            ->filter(fn (string $day) => in_array($day, static::DAY_KEYS, true))
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return static::defaultAttributes()['working_days_json'];
        }

        return collect(static::DAY_KEYS)
            ->filter(fn (string $day) => $normalized->contains($day))
            ->values()
            ->all();
    }

    public static function normalizeHolidays(array $dates): array
    {
        return collect($dates)
            ->map(fn ($date) => trim((string) $date))
            ->filter(fn (string $date) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}


