<?php

namespace Ajustatech\Financial\Tests\Feature\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
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

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            'payment_method_type' => null,
            'company_cash_id' => $managerialCash->id,
            'is_active' => true,
        ]);

        $payable = $service->createPayable(
            'Fornecedor ABC',
            200,
            '2026-03-21'
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

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            'payment_method_type' => 'pix',
            'company_cash_id' => $managerialCash->id,
            'is_active' => true,
        ]);

        $receivable = $service->createReceivable(
            'Cliente XPTO',
            300,
            '2026-03-21',
            'pix'
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

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
            'payment_method_type' => 'dinheiro',
            'company_cash_id' => $managerialCash->id,
            'is_active' => true,
        ]);

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW,
            'payment_method_type' => 'dinheiro',
            'company_cash_id' => $managerialCash->id,
            'is_active' => true,
        ]);

        $session = $service->openDailySalesCash($user->id, 'dinheiro', 150);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'id' => $session->id,
            'user_id' => $user->id,
            'status' => 'open',
            'outflow_status' => 'completed',
        ]);

        $this->assertEquals(850.0, (float) $managerialCash->fresh()->calculateBalance());

        $service->closeDailySalesCash($user->id, 'dinheiro', 150);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'id' => $session->id,
            'status' => 'closed',
            'inflow_status' => 'completed',
        ]);

        $this->assertEquals(1000.0, (float) $managerialCash->fresh()->calculateBalance());
    }

    public function test_settling_payable_should_follow_current_route_even_when_record_has_old_cash(): void
    {
        $wrongCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Antigo',
            'balance_amount' => 1000,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        $routeCash = CompanyCash::createNew([
            'cash_name' => 'Caixa da Rota',
            'balance_amount' => 1000,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            'payment_method_type' => null,
            'company_cash_id' => $routeCash->id,
            'is_active' => true,
        ]);

        $payable = \Ajustatech\Financial\Database\Models\FinancialPayable::factory()->create([
            'company_cash_id' => $wrongCash->id,
            'amount' => 200,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
        ]);

        $service = app(FinancialFlowService::class);
        $service->settlePayable($payable->id);

        $this->assertEquals(1000.0, (float) $wrongCash->fresh()->calculateBalance());
        $this->assertEquals(800.0, (float) $routeCash->fresh()->calculateBalance());
        $this->assertDatabaseHas('financial_payables', [
            'id' => $payable->id,
            'company_cash_id' => $routeCash->id,
            'status' => 'paid',
        ]);
    }

    public function test_settling_receivable_should_follow_current_route_even_when_record_has_old_cash(): void
    {
        $wrongCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Antigo Recebimento',
            'balance_amount' => 500,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        $routeCash = CompanyCash::createNew([
            'cash_name' => 'Caixa da Rota Recebimento',
            'balance_amount' => 500,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        FinancialCashFlowRoute::create([
            'flow_key' => FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            'payment_method_type' => 'pix',
            'company_cash_id' => $routeCash->id,
            'is_active' => true,
        ]);

        $receivable = \Ajustatech\Financial\Database\Models\FinancialReceivable::factory()->create([
            'company_cash_id' => $wrongCash->id,
            'payment_method_type' => 'pix',
            'amount' => 300,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
        ]);

        $service = app(FinancialFlowService::class);
        $service->settleReceivable($receivable->id);

        $this->assertEquals(500.0, (float) $wrongCash->fresh()->calculateBalance());
        $this->assertEquals(800.0, (float) $routeCash->fresh()->calculateBalance());
        $this->assertDatabaseHas('financial_receivables', [
            'id' => $receivable->id,
            'company_cash_id' => $routeCash->id,
            'status' => 'received',
        ]);
    }
}
