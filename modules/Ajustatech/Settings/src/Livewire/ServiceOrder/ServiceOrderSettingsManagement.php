<?php

namespace Ajustatech\Settings\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
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

    public array $workingDays = [];

    public array $holidayDates = [];

    #[Locked]
    public array $dayOptions = [];

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
        $this->workingDays = (array) ($settings->working_days_json ?? []);
        $this->holidayDates = array_values((array) ($settings->holidays_json ?? []));
        $this->dayOptions = $this->settingsService->dayOptions();
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
            'workingDays' => ['required', 'array', 'min:1', 'max:7'],
            'workingDays.*' => ['required', 'in:'.implode(',', ServiceOrderSetting::DAY_KEYS)],
            'holidayDates' => ['nullable', 'array', 'max:366'],
            'holidayDates.*' => ['nullable', 'date_format:Y-m-d', 'distinct'],
        ];
    }

    public function addHolidayDate(): void
    {
        $this->holidayDates[] = '';
    }

    public function removeHolidayDate(int $index): void
    {
        if (! array_key_exists($index, $this->holidayDates)) {
            return;
        }

        unset($this->holidayDates[$index]);
        $this->holidayDates = array_values($this->holidayDates);
    }

    public function save()
    {
        $this->holidayDates = collect($this->holidayDates)
            ->map(fn ($date) => trim((string) $date))
            ->values()
            ->all();

        $validated = $this->validate();

        $holidays = collect((array) ($validated['holidayDates'] ?? []))
            ->map(fn ($date) => trim((string) $date))
            ->filter(fn (string $date) => $date !== '')
            ->unique()
            ->values()
            ->all();

        $this->settingsService->saveSettings([
            'initial_order_number' => (int) $validated['initialOrderNumber'],
            'working_days_json' => (array) $validated['workingDays'],
            'holidays_json' => $holidays,
        ]);

        return redirect()->route('settings-service-order-show');
    }

    public function render()
    {
        return view('settings::livewire.service-order.service-order-settings-management');
    }
}
