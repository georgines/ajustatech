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

    public array $holidays = [];

    public bool $isHolidayModalOpen = false;

    public string $holidayName = '';

    public string $holidayDate = '';

    public ?int $editingHolidayIndex = null;

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
        $this->holidays = ServiceOrderSetting::normalizeHolidays((array) ($settings->holidays_json ?? []));
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
            'holidays' => ['nullable', 'array', 'max:366'],
            'holidays.*.name' => ['required', 'string', 'min:2', 'max:100'],
            'holidays.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
        ];
    }

    protected function holidayModalRules(): array
    {
        return [
            'holidayName' => ['required', 'string', 'min:2', 'max:100'],
            'holidayDate' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function openHolidayModal(): void
    {
        $this->resetValidation(['holidayName', 'holidayDate']);
        $this->holidayName = '';
        $this->holidayDate = '';
        $this->editingHolidayIndex = null;
        $this->isHolidayModalOpen = true;
    }

    public function openHolidayEditModal(int $index): void
    {
        if (! array_key_exists($index, $this->holidays)) {
            return;
        }

        $this->resetValidation(['holidayName', 'holidayDate']);
        $this->holidayName = (string) data_get($this->holidays, "{$index}.name", '');
        $this->holidayDate = (string) data_get($this->holidays, "{$index}.date", '');
        $this->editingHolidayIndex = $index;
        $this->isHolidayModalOpen = true;
    }

    public function closeHolidayModal(): void
    {
        $this->isHolidayModalOpen = false;
        $this->resetValidation(['holidayName', 'holidayDate']);
        $this->holidayName = '';
        $this->holidayDate = '';
        $this->editingHolidayIndex = null;
    }

    public function saveHolidayFromModal(): void
    {
        $validated = $this->validate($this->holidayModalRules());

        $alreadyExists = collect($this->holidays)
            ->contains(function (array $holiday, int $index) use ($validated): bool {
                if ($this->editingHolidayIndex !== null && $index === $this->editingHolidayIndex) {
                    return false;
                }

                return ($holiday['date'] ?? '') === $validated['holidayDate'];
            });

        if ($alreadyExists) {
            $this->addError('holidayDate', trans('validation.distinct', ['attribute' => trans('settings::messages.holiday_date_label')]));

            return;
        }

        $holidayData = [
            'name' => trim($validated['holidayName']),
            'date' => trim($validated['holidayDate']),
        ];

        if ($this->editingHolidayIndex !== null && array_key_exists($this->editingHolidayIndex, $this->holidays)) {
            $this->holidays[$this->editingHolidayIndex] = $holidayData;
        } else {
            $this->holidays[] = $holidayData;
        }

        $this->holidays = ServiceOrderSetting::normalizeHolidays($this->holidays);
        $this->closeHolidayModal();
    }

    public function removeHoliday(int $index): void
    {
        if (! array_key_exists($index, $this->holidays)) {
            return;
        }

        unset($this->holidays[$index]);
        $this->holidays = array_values($this->holidays);
    }

    public function save()
    {
        $this->holidays = collect($this->holidays)
            ->map(fn ($holiday) => [
                'name' => trim((string) data_get($holiday, 'name', '')),
                'date' => trim((string) data_get($holiday, 'date', '')),
            ])
            ->reject(fn (array $holiday) => $holiday['name'] === '' && $holiday['date'] === '')
            ->values()
            ->all();

        $validated = $this->validate();

        $this->settingsService->saveSettings([
            'initial_order_number' => (int) $validated['initialOrderNumber'],
            'working_days_json' => (array) $validated['workingDays'],
            'holidays_json' => ServiceOrderSetting::normalizeHolidays((array) ($validated['holidays'] ?? [])),
        ]);

        return redirect()->route('settings-service-order-show');
    }

    public function render()
    {
        return view('settings::livewire.service-order.service-order-settings-management');
    }
}
