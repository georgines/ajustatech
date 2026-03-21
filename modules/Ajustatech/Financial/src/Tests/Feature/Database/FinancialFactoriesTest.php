<?php

namespace Ajustatech\Financial\Tests\Feature\Database;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Database\Models\FinancialCashFlowRoute;
use Ajustatech\Financial\Database\Models\FinancialPayable;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethodCost;
use Ajustatech\Financial\Database\Models\FinancialReceivable;
use Ajustatech\Financial\Database\Models\SalesCashSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialFactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_financial_card_brand_using_factory(): void
    {
        $brand = FinancialCardBrand::factory()->create();

        $this->assertDatabaseHas('financial_card_brands', [
            'id' => $brand->id,
            'name' => $brand->name,
        ]);
    }

    public function test_can_create_payment_method_with_costs_using_factories(): void
    {
        $method = FinancialPaymentMethod::factory()
            ->has(FinancialPaymentMethodCost::factory()->count(2), 'costs')
            ->create();

        $this->assertDatabaseHas('financial_payment_methods', [
            'id' => $method->id,
            'type' => $method->type,
        ]);

        $this->assertCount(2, $method->fresh()->costs);
    }

    public function test_can_create_payable_and_receivable_using_factories(): void
    {
        $payable = FinancialPayable::factory()->create();
        $receivable = FinancialReceivable::factory()->create();

        $this->assertDatabaseHas('financial_payables', [
            'id' => $payable->id,
            'company_cash_id' => $payable->company_cash_id,
        ]);

        $this->assertDatabaseHas('financial_receivables', [
            'id' => $receivable->id,
            'company_cash_id' => $receivable->company_cash_id,
        ]);
    }

    public function test_can_create_cash_flow_route_using_factory(): void
    {
        $route = FinancialCashFlowRoute::factory()->create();

        $this->assertDatabaseHas('financial_cash_flow_routes', [
            'id' => $route->id,
            'flow_key' => $route->flow_key,
            'company_cash_id' => $route->company_cash_id,
        ]);
    }

    public function test_can_create_sales_cash_session_using_factory(): void
    {
        $session = SalesCashSession::factory()->create();

        $this->assertDatabaseHas('sales_cash_sessions', [
            'id' => $session->id,
            'user_id' => $session->user_id,
            'status' => 'open',
        ]);
    }
}
