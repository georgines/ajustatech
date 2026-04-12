<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\CompanyHours;

use Ajustatech\Settings\Database\Models\CompanyHours\CompanyHour;
use Ajustatech\Settings\Livewire\CompanyHours\CompanyHoursSettingsManagement;
use Ajustatech\Settings\Services\CompanyHours\Contracts\CompanyHoursSettingsServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyHoursSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_route_renders_management_screen_directly(): void
    {
        $this->get(route('settings-company-hours-show'))
            ->assertOk()
            ->assertSeeText(trans('settings::messages.company_hours_form_title'))
            ->assertSeeText(trans('settings::messages.company_open_days'))
            ->assertSeeText(trans('settings::messages.company_holidays'));
    }

    public function test_initial_render_uses_minimum_queries(): void
    {
        CompanyHour::singleton();

        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            if (str_contains($query->sql, 'company_hours')) {
                $queries[] = $query->sql;
            }
        });

        $this->app->make(CompanyHoursSettingsServiceInterface::class)->getSettings();

        $this->assertLessThanOrEqual(3, count($queries));
    }

    public function test_locked_properties_cannot_be_tampered(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->assertSet('title', trans('settings::messages.company_hours_edit_title'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot update locked property: [title]');

        Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('title', 'Qualquer valor');
    }

    public function test_can_update_company_hours_and_holidays(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('workingDays', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Natal', 'date' => '2026-12-25'],
            ])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('company-hours-saved');

        $companyHour = CompanyHour::singleton();

        $this->assertDatabaseHas('company_hours', [
            'id' => $companyHour->id,
        ]);
        $this->assertDatabaseHas('company_hours_working_days', [
            'company_hour_id' => $companyHour->id,
            'day_key' => 'monday',
        ]);
        $this->assertDatabaseHas('company_hours_holidays', [
            'company_hour_id' => $companyHour->id,
            'holiday_name' => 'Natal',
            'holiday_date' => '2026-12-25',
        ]);
    }

    public function test_save_uses_minimum_queries_when_persisting_existing_settings(): void
    {
        CompanyHour::singleton();

        $component = Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('workingDays', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
                ['name' => 'Natal', 'date' => '2026-12-25'],
            ]);

        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $component
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('company-hours-saved');

        $this->assertLessThanOrEqual(5, count($queries));
    }

    public function test_validates_invalid_payload(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('workingDays', [])
            ->set('holidays', [
                ['name' => '', 'date' => 'invalid-date'],
            ])
            ->call('save')
            ->assertHasErrors([
                'workingDays' => 'required',
                'holidays.0.name' => 'required',
                'holidays.0.date' => 'date_format',
            ]);
    }

    public function test_adds_holiday_using_modal_fields(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->call('openHolidayModal')
            ->set('holidayName', 'Corpus Christi')
            ->set('holidayDate', '2026-06-04')
            ->call('saveHolidayFromModal')
            ->assertSet('isHolidayModalOpen', false)
            ->assertSet('holidays.0.name', 'Corpus Christi')
            ->assertSet('holidays.0.date', '2026-06-04');
    }

    public function test_confirm_remove_holiday_dispatches_switchalert(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
            ])
            ->call('confirmRemoveHoliday', 0)
            ->assertDispatched('confirmation');
    }

    public function test_remove_holiday_after_confirmation_removes_item(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
            ->set('holidays', [
                ['name' => 'Confraternizacao Universal', 'date' => '2026-01-01'],
            ])
            ->dispatch('remove-holiday', index: 0)
            ->assertSet('holidays', []);
    }

    public function test_blocks_duplicate_holiday_date_in_modal(): void
    {
        Livewire::test(CompanyHoursSettingsManagement::class)
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
        Livewire::test(CompanyHoursSettingsManagement::class)
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
