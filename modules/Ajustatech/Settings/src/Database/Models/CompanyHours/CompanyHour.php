<?php

namespace Ajustatech\Settings\Database\Models\CompanyHours;

use Ajustatech\Settings\Database\Factories\CompanyHours\CompanyHourFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CompanyHour extends Model
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

    protected $table = 'company_hours';

    protected $fillable = [];

    protected static function newFactory()
    {
        return CompanyHourFactory::new();
    }

    public function workingDays(): HasMany
    {
        return $this->hasMany(CompanyHourWorkingDay::class, 'company_hour_id')->orderBy('sort_order');
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(CompanyHourHoliday::class, 'company_hour_id')->orderBy('holiday_date');
    }

    public static function singleton(): self
    {
        $companyHour = static::query()->first();

        if ($companyHour !== null) {
            return $companyHour->loadMissing(['workingDays', 'holidays']);
        }

        $companyHour = new self;
        $companyHour->save();
        $companyHour->syncWorkingDays(static::defaultWorkingDays());

        return $companyHour->load(['workingDays', 'holidays']);
    }

    public static function updateSingleton(array $attributes): self
    {
        $companyHour = static::query()->first();

        if ($companyHour === null) {
            $companyHour = new self;
            $companyHour->save();
        }

        $workingDayRows = $companyHour->syncWorkingDays((array) ($attributes['working_days'] ?? []));
        $holidayRows = $companyHour->syncHolidays((array) ($attributes['holidays'] ?? []));

        return $companyHour
            ->setRelation('workingDays', CompanyHourWorkingDay::hydrate($workingDayRows))
            ->setRelation('holidays', CompanyHourHoliday::hydrate($holidayRows));
    }

    public static function defaultWorkingDays(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }

    public static function normalizeWorkingDays(array $days): array
    {
        $normalized = collect($days)
            ->map(fn ($day) => strtolower(trim((string) $day)))
            ->filter(fn (string $day) => in_array($day, static::DAY_KEYS, true))
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return static::defaultWorkingDays();
        }

        return collect(static::DAY_KEYS)
            ->filter(fn (string $day) => $normalized->contains($day))
            ->values()
            ->all();
    }

    public static function normalizeHolidays(array $holidays): array
    {
        return collect($holidays)
            ->map(function ($holiday): array {
                if (is_array($holiday)) {
                    return [
                        'name' => trim((string) ($holiday['name'] ?? '')),
                        'date' => trim((string) ($holiday['date'] ?? '')),
                    ];
                }

                return [
                    'name' => 'Feriado',
                    'date' => trim((string) $holiday),
                ];
            })
            ->filter(function (array $holiday): bool {
                return $holiday['name'] !== ''
                    && preg_match('/^\d{4}-\d{2}-\d{2}$/', $holiday['date']) === 1;
            })
            ->unique('date')
            ->sortBy('date')
            ->values()
            ->all();
    }

    public function syncWorkingDays(array $days): array
    {
        $normalized = static::normalizeWorkingDays($days);

        CompanyHourWorkingDay::query()
            ->where('company_hour_id', $this->id)
            ->delete();

        $rows = collect($normalized)
            ->values()
            ->map(fn (string $day, int $index) => [
                'id' => (string) Str::uuid(),
                'company_hour_id' => $this->id,
                'day_key' => $day,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            CompanyHourWorkingDay::query()->insert($rows);
        }

        return $rows;
    }

    public function syncHolidays(array $holidays): array
    {
        $normalized = static::normalizeHolidays($holidays);

        CompanyHourHoliday::query()
            ->where('company_hour_id', $this->id)
            ->delete();

        $rows = collect($normalized)
            ->map(fn (array $holiday) => [
                'id' => (string) Str::uuid(),
                'company_hour_id' => $this->id,
                'holiday_name' => $holiday['name'],
                'holiday_date' => $holiday['date'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            CompanyHourHoliday::query()->insert($rows);
        }

        return $rows;
    }
}
