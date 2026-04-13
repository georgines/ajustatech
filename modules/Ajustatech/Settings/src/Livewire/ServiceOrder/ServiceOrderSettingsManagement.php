<?php

namespace Ajustatech\Settings\Livewire\ServiceOrder;

use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceOrderSettingsManagement extends Component
{
    #[Locked]
    public string $title = '';

    public int $initialOrderNumber = 1000;

    #[Locked]
    public array $statusFlows = [];

    protected ServiceOrderSettingsServiceInterface $settingsService;

    public function boot(ServiceOrderSettingsServiceInterface $service): void
    {
        $this->settingsService = $service;
    }

    public function mount(): void
    {
        $this->title = trans('settings::messages.service_order_settings_edit_title');

        $settings = $this->settingsService->getSettings();

        $this->initialOrderNumber = (int) $settings->initial_order_number;
        $this->statusFlows = $this->settingsService->listStatusFlows()
            ->map(fn ($flow) => [
                'code' => $flow->code,
                'name' => $flow->name,
                'description' => trans("settings::messages.service_order_status_flow_description_{$flow->code}"),
                'is_default_initial' => (bool) $flow->is_default_initial,
                'is_terminal' => (bool) $flow->is_terminal,
            ])
            ->all();
    }

    protected function rules(): array
    {
        return [
            'initialOrderNumber' => ['required', 'integer', 'min:1', 'max:999999999'],
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        $settings = $this->settingsService->saveSettings([
            'initial_order_number' => (int) $validated['initialOrderNumber'],
        ]);

        $this->initialOrderNumber = (int) $settings->initial_order_number;

        $this->dispatch('settings-saved', [
            'message' => trans('settings::messages.service_order_settings_saved_success'),
        ]);
    }

    public function render()
    {
        return view('settings::livewire.service-order.service-order-settings-management');
    }
}
