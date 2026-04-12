<?php

namespace Ajustatech\Settings\Tests\Feature\Database\ServiceOrder;

use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceOrderSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_service_order_setting_with_days_and_holidays(): void
    {
        $setting = ServiceOrderSetting::factory()->create([
            'initial_order_number' => 1200,
            'working_days_json' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'holidays_json' => [
                ['name' => 'Natal', 'date' => '2026-12-25'],
            ],
        ]);

        $this->assertDatabaseHas('service_order_settings', [
            'id' => $setting->id,
            'initial_order_number' => 1200,
        ]);
    }

    public function test_can_create_status_flow_item(): void
    {
        $status = ServiceOrderStatusFlow::factory()->create([
            'code' => 'aguardando_peca',
            'name' => 'Aguardando peca',
            'sort_order' => 5,
            'is_terminal' => false,
        ]);

        $this->assertDatabaseHas('service_order_status_flows', [
            'id' => $status->id,
            'code' => 'aguardando_peca',
        ]);
    }
}


