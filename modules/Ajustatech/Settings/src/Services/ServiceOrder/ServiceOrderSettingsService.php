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
        ServiceOrderStatusFlow::ensureDefaultRows();

        return ServiceOrderStatusFlow::listForSettings();
    }

    public function saveSettings(array $payload): ServiceOrderSetting
    {
        return ServiceOrderSetting::updateSingleton([
            'initial_order_number' => (int) ($payload['initial_order_number'] ?? 1),
            'working_days_json' => (array) ($payload['working_days_json'] ?? []),
            'holidays_json' => (array) ($payload['holidays_json'] ?? []),
        ]);
    }

    public function dayOptions(): array
    {
        return collect(ServiceOrderSetting::DAY_KEYS)
            ->map(fn (string $day) => [
                'value' => $day,
                'label' => trans("settings::messages.weekday_{$day}"),
            ])
            ->all();
    }
}

