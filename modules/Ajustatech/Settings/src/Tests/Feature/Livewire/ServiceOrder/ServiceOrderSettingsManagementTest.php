<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrderSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_route_renders_management_screen_directly(): void
    {
        $this->get(route('settings-service-order-show'))
            ->assertOk()
            ->assertSeeText(trans('settings::messages.service_order_settings_form_title'))
            ->assertSeeText(trans('settings::messages.service_order_status_flow_title'));
    }

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
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('settings-service-order-show'));

        $this->assertDatabaseHas('service_order_settings', [
            'initial_order_number' => 3000,
        ]);
    }

    public function test_validates_invalid_settings_payload(): void
    {
        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 0)
            ->call('save')
            ->assertHasErrors([
                'initialOrderNumber' => 'min',
            ]);
    }
}
