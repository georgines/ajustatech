<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrderSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_locked_properties_cannot_be_tampered(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->assertSet('title', trans('settings::messages.service_order_settings_edit_title'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot update locked property: [title]');

        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('title', 'Qualquer valor');
    }

    public function test_can_update_service_order_settings(): void
    {
        ServiceOrderStatusFlow::factory()->count(2)->create();

        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->set('holidayDates', ['2026-01-01', '2026-11-02'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings-service-order-show'));

        $this->assertDatabaseHas('service_order_settings', [
            'initial_order_number' => 3000,
        ]);

        $this->assertDatabaseHas('service_order_settings', [
            'holidays_json' => json_encode(['2026-01-01', '2026-11-02']),
        ]);
    }

    public function test_validates_invalid_settings_payload(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 0)
            ->set('workingDays', [])
            ->set('holidayDates', ['invalid-date'])
            ->call('save')
            ->assertHasErrors([
                'initialOrderNumber' => 'min',
                'workingDays' => 'required',
                'holidayDates.0' => 'date_format',
            ]);
    }

    public function test_validates_duplicate_holiday_dates(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday'])
            ->set('holidayDates', ['2026-01-01', '2026-01-01'])
            ->call('save')
            ->assertHasErrors([
                'holidayDates.1' => 'distinct',
            ]);
    }

    public function test_persists_holidays_sanitized_sorted_and_unique(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday'])
            ->set('holidayDates', ['2026-12-25', ' 2026-01-01 ', ''])
            ->call('save')
            ->assertHasNoErrors();

        $setting = ServiceOrderSetting::singleton();

        $this->assertSame(['2026-01-01', '2026-12-25'], (array) $setting->holidays_json);
    }
}
