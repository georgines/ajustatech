<?php

namespace Ajustatech\Financial\Tests\Feature\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Services\FinancialFlowService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialFlowServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_settling_payable_should_create_outflow_in_managerial_cash(): void
    {
        $managerialCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Gerencial',
            'balance_amount' => 1000,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        $service = app(FinancialFlowService::class);

        $payable = $service->createPayable(
            'Fornecedor ABC',
            200,
            '2026-03-21',
            $managerialCash->id
        );

        $service->settlePayable($payable->id);

        $this->assertDatabaseHas('financial_payables', [
            'id' => $payable->id,
            'status' => 'paid',
            'cash_flow_status' => 'completed',
        ]);

        $this->assertEquals(800.0, (float) $managerialCash->fresh()->calculateBalance());
    }

    public function test_settling_receivable_should_create_inflow_in_managerial_cash(): void
    {
        $managerialCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Gerencial',
            'balance_amount' => 500,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        $service = app(FinancialFlowService::class);

        $receivable = $service->createReceivable(
            'Cliente XPTO',
            300,
            '2026-03-21',
            $managerialCash->id
        );

        $service->settleReceivable($receivable->id);

        $this->assertDatabaseHas('financial_receivables', [
            'id' => $receivable->id,
            'status' => 'received',
            'cash_flow_status' => 'completed',
        ]);

        $this->assertEquals(800.0, (float) $managerialCash->fresh()->calculateBalance());
    }

    public function test_sales_cash_daily_open_and_close_should_move_money_from_and_back_to_managerial_cash(): void
    {
        $user = User::factory()->create();

        $managerialCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Gerencial',
            'balance_amount' => 1000,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        $service = app(FinancialFlowService::class);

        $session = $service->openDailySalesCash($user->id, $managerialCash->id, 150);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'id' => $session->id,
            'user_id' => $user->id,
            'status' => 'open',
            'outflow_status' => 'completed',
        ]);

        $this->assertEquals(850.0, (float) $managerialCash->fresh()->calculateBalance());

        $service->closeDailySalesCash($user->id, 150);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'id' => $session->id,
            'status' => 'closed',
            'inflow_status' => 'completed',
        ]);

        $this->assertEquals(1000.0, (float) $managerialCash->fresh()->calculateBalance());
    }
}

