<?php

namespace Ajustatech\Settings\Services\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Ajustatech\Settings\Services\CompanyHours\Contracts\CompanyHoursSettingsServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyHoursSettingsService implements CompanyHoursSettingsServiceInterface
{
    private ?CompanyHour $settingsCache = null;

    public function getSettings(): CompanyHour
    {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }

        return $this->settingsCache = CompanyHour::singleton();
    }

    public function saveSettings(array $payload): CompanyHour
    {
        $validated = $this->validatePayload($payload);

        return DB::transaction(function () use ($validated) {
            return $this->settingsCache = CompanyHour::updateSingleton([
                'working_days' => $validated['working_days'],
                'holidays' => $validated['holidays'] ?? [],
            ]);
        });
    }

    public function dayOptions(): array
    {
        return collect(CompanyHour::DAY_KEYS)
            ->map(fn (string $day) => [
                'value' => $day,
                'label' => trans("settings::messages.weekday_{$day}"),
            ])
            ->all();
    }

    private function validatePayload(array $payload): array
    {
        $validated = Validator::make($payload, [
            'working_days' => ['required', 'array', 'min:1', 'max:7'],
            'working_days.*' => ['required', 'string', 'distinct', 'in:'.implode(',', CompanyHour::DAY_KEYS)],
            'holidays' => ['nullable', 'array', 'max:366'],
            'holidays.*.name' => ['required', 'string', 'min:2', 'max:100'],
            'holidays.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
        ])->validate();

        return [
            'working_days' => CompanyHour::normalizeWorkingDays((array) ($validated['working_days'] ?? [])),
            'holidays' => CompanyHour::normalizeHolidays((array) ($validated['holidays'] ?? [])),
        ];
    }
}
