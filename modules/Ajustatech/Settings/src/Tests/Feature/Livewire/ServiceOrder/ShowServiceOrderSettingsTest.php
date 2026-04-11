<?php

namespace Ajustatech\Settings\Tests\Feature\Livewire\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Livewire\ServiceOrder\ShowServiceOrderSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowServiceOrderSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_service_order_settings_and_status_flows(): void
    {
        ServiceOrderSetting::factory()->create([
            'initial_order_number' => 2500,
            'working_days_json' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays_json' => ['2026-01-01', '2026-04-21'],
        ]);

        Livewire::test(ShowServiceOrderSettings::class)
            ->assertStatus(200)
            ->assertSee('2.500')
            ->assertSee('Entrada')
            ->assertSee('em_analise')
            ->assertSee('orcamento_reprovado');
    }
}


