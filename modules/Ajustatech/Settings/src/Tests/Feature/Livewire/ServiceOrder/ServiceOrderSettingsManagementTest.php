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
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Finados', 'date' => '2026-11-02'],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings-service-order-show'));

        $this->assertDatabaseHas('service_order_settings', [
            'initial_order_number' => 3000,
        ]);

        $this->assertDatabaseHas('service_order_settings', [
            'holidays_json' => json_encode([
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Finados', 'date' => '2026-11-02'],
            ]),
        ]);
    }

    public function test_validates_invalid_settings_payload(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 0)
            ->set('workingDays', [])
            ->set('holidays', [
                ['name' => '', 'date' => 'invalid-date'],
            ])
            ->call('save')
            ->assertHasErrors([
                'initialOrderNumber' => 'min',
                'workingDays' => 'required',
                'holidays.0.name' => 'required',
                'holidays.0.date' => 'date_format',
            ]);
    }

    public function test_validates_duplicate_holiday_dates(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday'])
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Ano Novo Extra', 'date' => '2026-01-01'],
            ])
            ->call('save')
            ->assertHasErrors([
                'holidays.1.date' => 'distinct',
            ]);
    }

    public function test_persists_holidays_sanitized_sorted_and_unique(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday'])
            ->set('holidays', [
                ['name' => ' Natal ', 'date' => '2026-12-25'],
                ['name' => ' Confraternizacao Universal ', 'date' => ' 2026-01-01 '],
                ['name' => '', 'date' => ''],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $setting = ServiceOrderSetting::singleton();

        $this->assertSame([
            ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
            ['name' => 'Natal', 'date' => '2026-12-25'],
        ], (array) $setting->holidays_json);
    }

    public function test_adds_holiday_using_modal_fields(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->call('openHolidayModal')
            ->set('holidayName', 'Corpus Christi')
            ->set('holidayDate', '2026-06-04')
            ->call('saveHolidayFromModal')
            ->assertSet('isHolidayModalOpen', false)
            ->assertSet('holidays.0.name', 'Corpus Christi')
            ->assertSet('holidays.0.date', '2026-06-04');
    }

    public function test_blocks_duplicate_holiday_date_in_modal(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
            ])
            ->call('openHolidayModal')
            ->set('holidayName', 'Ano Novo')
            ->set('holidayDate', '2026-01-01')
            ->call('saveHolidayFromModal')
            ->assertHasErrors([
                'holidayDate',
            ]);
    }

    public function test_edits_holiday_using_modal_fields(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('holidays', [
                ['name' => 'Natal', 'date' => '2026-12-25'],
            ])
            ->call('openHolidayEditModal', 0)
            ->assertSet('holidayName', 'Natal')
            ->assertSet('holidayDate', '2026-12-25')
            ->set('holidayName', 'Natal Nacional')
            ->set('holidayDate', '2026-12-24')
            ->call('saveHolidayFromModal')
            ->assertSet('isHolidayModalOpen', false)
            ->assertSet('holidays.0.name', 'Natal Nacional')
            ->assertSet('holidays.0.date', '2026-12-24');
    }
}
