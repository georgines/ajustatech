<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Ajustatech\Settings\Livewire\ServiceOrder\ServiceOrderSettingsManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceOrderSettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_route_renders_management_screen_directly(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $this->get(route('settings-service-order-show'))
            ->assertOk()
            ->assertSeeText(trans('settings::messages.service_order_settings_form_title'))
            ->assertSeeText(trans('settings::messages.service_order_status_flow_title'));
    }

    public function test_show_route_uses_minimum_queries_without_writing_status_rows(): void
    {
        ServiceOrderStatusFlow::ensureDefaultRows();

        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        $this->get(route('settings-service-order-show'))->assertOk();

        $this->assertCount(0, array_filter($queries, fn (string $query): bool => str_contains($query, 'insert into `service_order_status_flows`') || str_contains($query, 'update `service_order_status_flows`')));
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
        ServiceOrderStatusFlow::ensureDefaultRows();

        Livewire::test(ServiceOrderSettingsManagement::class)
            ->set('initialOrderNumber', 3000)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settings-saved')
            ->assertSet('initialOrderNumber', 3000);

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
