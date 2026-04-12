<?php

namespace Ajustatech\Settings\Services\Company\Contracts;

use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Illuminate\Http\UploadedFile;

interface CompanySettingsServiceInterface
{
    public function getSettings(): CompanySetting;

    public function saveSettings(array $payload, ?UploadedFile $logo = null): CompanySetting;

    public function validateLogo(UploadedFile $logo): void;

    public function acceptAttribute(): string;
}
