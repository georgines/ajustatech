<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\CashRoutes;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Livewire\CashFlowRouteManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashFlowRouteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_cash_route_for_payables(): void
    {
        $cash = CompanyCash::createNew([
            'cash_name' => 'Caixa Gerencial',
            'balance_amount' => 100,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        Livewire::test(CashFlowRouteManagement::class)
            ->set('flowKey', FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW)
            ->set('companyCashId', $cash->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'flow_key' => FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            'company_cash_id' => $cash->id,
        ]);
    }
}