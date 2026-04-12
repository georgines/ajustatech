<?php

namespace Ajustatech\Settings\Services\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Illuminate\Support\Collection;

class ServiceOrderSettingsService implements ServiceOrderSettingsServiceInterface
{
    public function getSettings(): ServiceOrderSetting
    {
        return ServiceOrderSetting::singleton();
    }

    public function listStatusFlows(): Collection
    {
        return ServiceOrderStatusFlow::listForSettings();
    }

    public function saveSettings(array $payload): ServiceOrderSetting
    {
        return ServiceOrderSetting::updateSingleton([
            'initial_order_number' => (int) ($payload['initial_order_number'] ?? 1),
        ]);
    }
}
