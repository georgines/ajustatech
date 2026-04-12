<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\ServiceOrder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ShowServiceOrderSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_path_renders_management_screen(): void
    {
        $this->get(route('settings-service-order-show'))
            ->assertOk()
            ->assertSeeText(trans('settings::messages.service_order_settings_form_title'))
            ->assertSeeText(trans('settings::messages.service_order_status_flow_title'))
            ->assertSeeText(trans('settings::messages.service_order_status_flow_description_entrada'))
            ->assertSeeText(trans('settings::messages.service_order_status_flow_description_concluido'));
    }

    public function test_edit_settings_path_resolves_to_settings_route(): void
    {
        $route = app('router')
            ->getRoutes()
            ->match(Request::create('/ordens-servico/configuracoes/editar', 'GET'));

        $this->assertSame('settings-service-order-edit', $route->getName());
    }

    public function test_edit_settings_path_redirects_to_single_settings_screen(): void
    {
        $this->get(route('settings-service-order-edit'))
            ->assertRedirect(route('settings-service-order-show'));
    }
}


