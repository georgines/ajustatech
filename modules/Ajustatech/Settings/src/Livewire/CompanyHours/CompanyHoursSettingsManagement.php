<?php

namespace Ajustatech\Settings\Livewire\CompanyHours;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Ajustatech\Settings\Services\CompanyHours\Contracts\CompanyHoursSettingsServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class CompanyHoursSettingsManagement extends Component
{
    use SwitchAlertDispatch;

    #[Locked]
    public string $title = '';

    public array $workingDays = [];

    public array $holidays = [];

    public bool $isHolidayModalOpen = false;

    public string $holidayName = '';

    public string $holidayDate = '';

    public ?int $editingHolidayIndex = null;

    #[Locked]
    public array $dayOptions = [];

    protected CompanyHoursSettingsServiceInterface $settingsService;

    public function boot(CompanyHoursSettingsServiceInterface $service): void
    {
        $this->settingsService = $service;
    }

    public function mount(): void
    {
        $this->title = trans('settings::messages.company_hours_edit_title');

        $settings = $this->settingsService->getSettings();

        $this->workingDays = $settings->workingDays
            ->sortBy('sort_order')
            ->pluck('day_key')
            ->values()
            ->all();
        $this->holidays = $settings->holidays
            ->sortBy('holiday_date')
            ->map(fn ($holiday) => [
                'name' => (string) $holiday->holiday_name,
                'date' => (string) $holiday->holiday_date,
            ])
            ->values()
            ->all();
        $this->dayOptions = $this->settingsService->dayOptions();
    }

    protected function rules(): array
    {
        return [
            'workingDays' => ['required', 'array', 'min:1', 'max:7'],
            'workingDays.*' => ['required', 'in:'.implode(',', CompanyHour::DAY_KEYS)],
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

        $this->holidays = CompanyHour::normalizeHolidays($this->holidays);
        $this->closeHolidayModal();
    }

    public function confirmRemoveHoliday(int $index): void
    {
        if (! array_key_exists($index, $this->holidays)) {
            return;
        }

        $this->dispatchConfirmation(trans('settings::messages.confirm_delete_holiday'))
            ->to('remove-holiday', index: $index)
            ->typeWarning()
            ->setButtonOK(trans('settings::messages.confirm_yes'))
            ->setButtonCancel(trans('settings::messages.confirm_no'))
            ->run();
    }

    #[On('remove-holiday')]
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

        $savedSettings = $this->settingsService->saveSettings([
            'working_days' => (array) $validated['workingDays'],
            'holidays' => CompanyHour::normalizeHolidays((array) ($validated['holidays'] ?? [])),
        ]);

        $this->workingDays = $savedSettings->workingDays
            ->sortBy('sort_order')
            ->pluck('day_key')
            ->values()
            ->all();

        $this->holidays = $savedSettings->holidays
            ->sortBy('holiday_date')
            ->map(fn ($holiday) => [
                'name' => (string) $holiday->holiday_name,
                'date' => (string) $holiday->holiday_date,
            ])
            ->values()
            ->all();

        $this->dispatch('company-hours-saved', [
            'message' => trans('settings::messages.company_hours_saved_success'),
        ]);
    }

    public function render()
    {
        return view('settings::livewire.company-hours.company-hours-settings-management');
    }
}
