<?php

namespace Ajustatech\ServiceOrder\Services\ServiceOrder;

use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCalendarServiceInterface;
use Ajustatech\Settings\Services\CompanyHours\Contracts\CompanyHoursSettingsServiceInterface;

class ServiceOrderCalendarService implements ServiceOrderCalendarServiceInterface
{
    public function __construct(
        protected CompanyHoursSettingsServiceInterface $companyHoursSettingsService,
    ) {}

    public function workingDays(): array
    {
        $settings = $this->companyHoursSettingsService->getSettings();

        return $settings->workingDays
            ->sortBy('sort_order')
            ->pluck('day_key')
            ->values()
            ->all();
    }

    public function holidays(): array
    {
        $settings = $this->companyHoursSettingsService->getSettings();

        return $settings->holidays
            ->sortBy('holiday_date')
            ->pluck('holiday_date')
            ->values()
            ->all();
    }
}
