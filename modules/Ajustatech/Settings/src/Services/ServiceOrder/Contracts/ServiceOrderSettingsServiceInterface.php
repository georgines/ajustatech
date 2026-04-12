<?php

namespace Ajustatech\Settings\Services\ServiceOrder\Contracts;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Illuminate\Support\Collection;

interface ServiceOrderSettingsServiceInterface
{
    public function getSettings(): ServiceOrderSetting;

    public function listStatusFlows(): Collection;

    public function saveSettings(array $payload): ServiceOrderSetting;
}
