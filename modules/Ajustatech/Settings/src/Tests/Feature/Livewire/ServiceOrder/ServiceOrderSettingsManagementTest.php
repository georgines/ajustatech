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

    public function test_can_update_service_order_settings(): void
    {
        ServiceOrderStatusFlow::factory()->count(2)->create();

        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->set('workingDays', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->set('holidayDates', ['2026-01-01', '2026-11-02'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('service-order-settings-show'));

        $this->assertDatabaseHas('service_order_settings', [
            'initial_order_number' => 3000,
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
}


