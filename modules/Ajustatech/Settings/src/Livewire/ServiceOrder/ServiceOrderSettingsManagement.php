<?php

namespace Ajustatech\Settings\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Services\ServiceOrder\Contracts\ServiceOrderSettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceOrderSettingsManagement extends Component
{
    public string $title = '';

    public int $initialOrderNumber = 1000;

    public array $workingDays = [];

    public array $holidayDates = [];

    public array $dayOptions = [];

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
                'name' => $flow->name,
                'is_terminal' => (bool) $flow->is_terminal,
            ])
            ->all();
    }

    protected function rules(): array
    {
        return [
            'initialOrderNumber' => ['required', 'integer', 'min:1', 'max:999999999'],
            'workingDays' => ['required', 'array', 'min:1'],
            'workingDays.*' => ['required', 'in:' . implode(',', ServiceOrderSetting::DAY_KEYS)],
            'holidayDates' => ['nullable', 'array'],
            'holidayDates.*' => ['nullable', 'date_format:Y-m-d'],
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

        return redirect()->route('service-order-settings-show');
    }

    public function render()
    {
        return view('settings::livewire.service-order.service-order-settings-management');
    }
}


