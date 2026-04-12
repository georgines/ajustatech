<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\PaymentMethods;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Livewire\PaymentMethodManagement;
use Ajustatech\Financial\Livewire\ShowPaymentMethods;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentMethodManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_fields_should_appear_only_for_card_types(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);

        Livewire::test(PaymentMethodManagement::class)
            ->assertDontSee(trans('financial::messages.payment_method_receipt_channel'))
            ->set('type', 'cartao_credito')
            ->assertSee(trans('financial::messages.payment_method_receipt_channel'))
            ->assertSee('1x')
            ->assertSee('21x');
    }

    public function test_can_create_credit_card_payment_method_with_cost_rules(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);

        Livewire::test(PaymentMethodManagement::class)
            ->set('type', 'cartao_credito')
            ->set('name', 'Credito Visa')
            ->set('fixedCost', 1.99)
            ->set('percentCost', 3.5)
            ->set('brand', 'visa')
            ->set('installments', 6)
            ->set('receiptChannel', 'maquina')
            ->call('addCost')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_payment_methods', [
            'type' => 'cartao_credito',
            'name' => 'Credito Visa',
        ]);

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'brand' => 'visa',
            'installments' => 6,
            'receipt_channel' => 'maquina',
        ]);
    }

    public function test_can_edit_existing_payment_method(): void
    {
        FinancialCardBrand::create(['name' => 'mastercard', 'is_active' => true]);

        $method = FinancialPaymentMethod::create([
            'type' => 'pix',
            'name' => 'Pix',
            'is_active' => true,
        ]);

        $method->costs()->create([
            'fixed_cost' => 0,
            'percent_cost' => 1,
        ]);

        Livewire::test(PaymentMethodManagement::class, ['id' => $method->id])
            ->set('name', 'Pix Atualizado')
            ->set('type', 'cartao_debito')
            ->call('confirmRemoveCost', 0)
            ->dispatch('remove-cost-rule', index: 0)
            ->set('fixedCost', 0.2)
            ->set('percentCost', 1.89)
            ->set('brand', 'mastercard')
            ->set('installments', 1)
            ->set('receiptChannel', 'maquina')
            ->call('addCost')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_payment_methods', [
            'id' => $method->id,
            'type' => 'cartao_debito',
            'name' => 'Pix Atualizado',
        ]);

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'brand' => 'mastercard',
            'installments' => 1,
            'receipt_channel' => 'maquina',
        ]);
    }

    public function test_can_delete_payment_method_from_list(): void
    {
        $method = FinancialPaymentMethod::create([
            'type' => 'dinheiro',
            'name' => 'Dinheiro',
            'is_active' => true,
        ]);

        Livewire::test(ShowPaymentMethods::class)
            ->call('confirmDelete', $method->id)
            ->dispatch('delete-payment-method', id: $method->id);

        $this->assertDatabaseMissing('financial_payment_methods', [
            'id' => $method->id,
        ]);
    }

    public function test_can_edit_individual_credit_card_cost_rule(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);

        $method = FinancialPaymentMethod::create([
            'type' => 'cartao_credito',
            'name' => 'Credito',
            'is_active' => true,
        ]);

        $method->costs()->create([
            'fixed_cost' => 0.49,
            'percent_cost' => 3.20,
            'brand' => 'visa',
            'installments' => 1,
            'receipt_channel' => 'maquina',
        ]);

        $method->costs()->create([
            'fixed_cost' => 0.79,
            'percent_cost' => 4.10,
            'brand' => 'visa',
            'installments' => 6,
            'receipt_channel' => 'link',
        ]);

        $component = Livewire::test(PaymentMethodManagement::class, ['id' => $method->id]);

        $indexToEdit = collect($component->get('costs'))
            ->search(fn (array $cost) => ($cost['receipt_channel'] ?? null) === 'link' && (int) ($cost['installments'] ?? 0) === 6);

        $this->assertNotFalse($indexToEdit);

        $component
            ->call('editCost', (int) $indexToEdit)
            ->set('percentCost', 4.35)
            ->set('installments', 8)
            ->set('receiptChannel', 'link')
            ->call('updateCost')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'brand' => 'visa',
            'receipt_channel' => 'link',
            'installments' => 8,
            'percent_cost' => 4.35,
        ]);

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'brand' => 'visa',
            'receipt_channel' => 'maquina',
            'installments' => 1,
            'percent_cost' => 3.2,
        ]);
    }

    public function test_can_duplicate_individual_credit_card_cost_rule_and_edit_copy(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);

        $method = FinancialPaymentMethod::create([
            'type' => 'cartao_credito',
            'name' => 'Credito',
            'is_active' => true,
        ]);

        $method->costs()->create([
            'fixed_cost' => 0.49,
            'percent_cost' => 3.20,
            'brand' => 'visa',
            'installments' => 1,
            'receipt_channel' => 'maquina',
        ]);

        Livewire::test(PaymentMethodManagement::class, ['id' => $method->id])
            ->call('duplicateCost', 0)
            ->set('percentCost', 4.35)
            ->set('installments', 6)
            ->call('updateCost')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'brand' => 'visa',
            'receipt_channel' => 'maquina',
            'installments' => 1,
            'percent_cost' => 3.2,
        ]);

        $this->assertDatabaseHas('financial_payment_method_costs', [
            'financial_payment_method_id' => $method->id,
            'brand' => 'visa',
            'receipt_channel' => 'maquina',
            'installments' => 6,
            'percent_cost' => 4.35,
        ]);
    }

    public function test_card_brands_should_appear_in_brand_select(): void
    {
        FinancialCardBrand::create(['name' => 'visa', 'is_active' => true]);
        FinancialCardBrand::create(['name' => 'elo', 'is_active' => true]);

        Livewire::test(PaymentMethodManagement::class)
            ->set('type', 'cartao_credito')
            ->assertSee('visa')
            ->assertSee('elo');
    }
}
