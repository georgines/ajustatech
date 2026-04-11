<?php

namespace Ajustatech\Settings\Livewire\ServiceOrder;

use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceOrderSettings extends Component
{
    public string $title = '';

    public int $initialOrderNumber = 1000;

    public array $workingDays = [];

    public array $holidayDates = [];

    public array $statusFlows = [];

    public array $dayLabels = [];

    public function mount(ServiceOrderSettingsServiceInterface $service): void
    {
        $this->title = trans('settings::messages.service_order_settings_title');

        $settings = $service->getSettings();
        $this->initialOrderNumber = (int) $settings->initial_order_number;
        $this->workingDays = (array) ($settings->working_days_json ?? []);
        $this->holidayDates = (array) ($settings->holidays_json ?? []);

        $this->statusFlows = $service->listStatusFlows()
            ->map(fn ($flow) => [
                'name' => $flow->name,
                'code' => $flow->code,
                'sort_order' => (int) $flow->sort_order,
                'is_terminal' => (bool) $flow->is_terminal,
                'is_default_initial' => (bool) $flow->is_default_initial,
            ])
            ->all();

        $this->dayLabels = collect($service->dayOptions())
            ->mapWithKeys(fn (array $item) => [$item['value'] => $item['label']])
            ->all();
    }

    public function render()
    {
        return view('settings::livewire.service-order.show-service-order-settings');
    }
}


