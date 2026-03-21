<?php

namespace Ajustatech\Financial\Tests\Feature\Services;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Services\PaymentMethodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentMethodServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_cash_payment_method_with_basic_cost(): void
    {
        $service = app(PaymentMethodService::class);

        $method = $service->createMethodWithCost(
            'dinheiro',
            'Dinheiro',
            [
                'fixed_cost' => 0,
                'percent_cost' => 0,
            ]
        );

        $this->assertDatabaseHas('financial_payment_methods', [
            'id' => $method->id,
            'type' => 'dinheiro',
        ]);

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'fixed_cost' => 0,
            'percent_cost' => 0,
        ]);
    }

    public function test_card_method_must_require_brand_installments_and_receipt_channel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = app(PaymentMethodService::class);

        $service->createMethodWithCost(
            'cartao_credito',
            'Cartao de Credito',
            [
                'fixed_cost' => 1.99,
                'percent_cost' => 3.5,
            ]
        );
    }

    public function test_card_method_must_require_registered_brand(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = app(PaymentMethodService::class);

        $service->createMethodWithCost(
            'cartao_credito',
            'Cartao de Credito',
            [
                'fixed_cost' => 1.99,
                'percent_cost' => 3.5,
                'brand' => 'nao-cadastrada',
                'installments' => 6,
                'receipt_channel' => 'maquina',
            ]
        );
    }

    public function test_can_update_payment_method_and_replace_cost_rules(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);

        $service = app(PaymentMethodService::class);

        $method = $service->createMethodWithCost('pix', 'Pix Inicial', [
            'fixed_cost' => 0,
            'percent_cost' => 1.2,
        ]);

        $updated = $service->updateMethodWithCosts($method->id, 'cartao_credito', 'Credito Visa', [
            [
                'fixed_cost' => 0.49,
                'percent_cost' => 3.99,
                'brand' => 'visa',
                'installments' => 6,
                'receipt_channel' => 'link',
            ],
        ]);

        $this->assertEquals('cartao_credito', $updated->type);
        $this->assertEquals('Credito Visa', $updated->name);
        $this->assertCount(1, $updated->costs);

        $this->assertDatabaseHas('financial_payment_methods', [
            'id' => $method->id,
            'type' => 'cartao_credito',
            'name' => 'Credito Visa',
        ]);
    }

    public function test_can_delete_payment_method(): void
    {
        $service = app(PaymentMethodService::class);

        $method = $service->createMethodWithCost('dinheiro', 'Dinheiro', [
            'fixed_cost' => 0,
            'percent_cost' => 0,
        ]);

        $service->deleteMethod($method->id);

        $this->assertDatabaseMissing('financial_payment_methods', [
            'id' => $method->id,
        ]);
        $this->assertNull(FinancialPaymentMethod::find($method->id));
    }
}
