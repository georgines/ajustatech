<?php

namespace Ajustatech\Financial\Tests\Feature\Database;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Database\Models\FinancialPayable;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Database\Models\FinancialReceivable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_methods_seed_command_creates_default_methods_and_costs(): void
    {
        $this->artisan('module:seed-financial-payment-methods')->assertExitCode(0);

        $this->assertDatabaseHas('financial_payment_methods', [
            'type' => FinancialPaymentMethod::TYPE_DINHEIRO,
            'name' => 'Dinheiro',
        ]);

        $this->assertDatabaseHas('financial_payment_methods', [
            'type' => FinancialPaymentMethod::TYPE_PIX,
            'name' => 'Pix',
        ]);

        $this->assertDatabaseHas('financial_payment_methods', [
            'type' => FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
            'name' => 'Cartao Debito',
        ]);

        $this->assertDatabaseHas('financial_payment_methods', [
            'type' => FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
            'name' => 'Cartao Credito',
        ]);

        $this->assertDatabaseCount('financial_payment_method_costs', 7);
    }

    public function test_receivables_and_payables_seed_commands_create_records(): void
    {
        $this->artisan('module:seed-financial-receivables')->assertExitCode(0);
        $this->artisan('module:seed-financial-payables')->assertExitCode(0);

        $this->assertDatabaseCount('financial_receivables', 12);
        $this->assertDatabaseCount('financial_payables', 12);

        $defaultCash = CompanyCash::query()
            ->where('cash_name', 'Caixa Repellendus')
            ->where('is_managerial', true)
            ->first();

        $this->assertNotNull($defaultCash);
        $this->assertTrue($defaultCash->balances()->exists());
        $this->assertDatabaseHas('financial_receivables', ['company_cash_id' => $defaultCash->id]);
        $this->assertDatabaseHas('financial_payables', ['company_cash_id' => $defaultCash->id]);
    }

    public function test_cash_flow_routes_seed_command_creates_default_routes(): void
    {
        $this->artisan('module:seed-financial-cash-flow-routes')->assertExitCode(0);

        $defaultCash = CompanyCash::query()
            ->where('cash_name', 'Caixa Repellendus')
            ->where('is_managerial', true)
            ->first();

        $this->assertNotNull($defaultCash);

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'flow_key' => FinancialCashFlowRoute::FLOW_PAYABLE_OUTFLOW,
            'payment_method_type' => null,
            'company_cash_id' => $defaultCash->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'flow_key' => FinancialCashFlowRoute::FLOW_RECEIVABLE_INFLOW,
            'payment_method_type' => FinancialPaymentMethod::TYPE_PIX,
            'company_cash_id' => $defaultCash->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'flow_key' => FinancialCashFlowRoute::FLOW_SALES_OPEN_OUTFLOW,
            'payment_method_type' => FinancialPaymentMethod::TYPE_DINHEIRO,
            'company_cash_id' => $defaultCash->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'flow_key' => FinancialCashFlowRoute::FLOW_SALES_CLOSE_INFLOW,
            'payment_method_type' => FinancialPaymentMethod::TYPE_PIX,
            'company_cash_id' => $defaultCash->id,
            'is_active' => true,
        ]);
    }

    public function test_sales_cash_sessions_seed_command_creates_sessions(): void
    {
        $this->artisan('module:seed-sales-cash-sessions')->assertExitCode(0);

        $this->assertDatabaseCount('sales_cash_sessions', 6);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('sales_cash_sessions', [
            'status' => 'closed',
            'inflow_status' => 'completed',
        ]);
    }
}
