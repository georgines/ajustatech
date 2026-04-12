<?php

namespace Ajustatech\Settings\Services\CompanyHours\Contracts;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;

interface CompanyHoursSettingsServiceInterface
{
    public function getSettings(): CompanyHour;

    public function saveSettings(array $payload): CompanyHour;

    public function dayOptions(): array;
}
