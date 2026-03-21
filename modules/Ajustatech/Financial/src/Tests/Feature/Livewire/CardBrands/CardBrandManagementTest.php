<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\CardBrands;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Livewire\CardBrandManagement;
use Ajustatech\Financial\Livewire\ShowCardBrands;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CardBrandManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_card_brand(): void
    {
        Livewire::test(CardBrandManagement::class)
            ->set('name', 'Visa')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_card_brands', ['name' => 'Visa']);
    }

    public function test_can_edit_card_brand(): void
    {
        $brand = FinancialCardBrand::create(['name' => 'Master', 'is_active' => true]);

        Livewire::test(CardBrandManagement::class, ['id' => $brand->id])
            ->set('name', 'Mastercard')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('financial_card_brands', ['id' => $brand->id, 'name' => 'Mastercard']);
    }

    public function test_can_delete_card_brand_with_confirmation_event(): void
    {
        $brand = FinancialCardBrand::create(['name' => 'Elo', 'is_active' => true]);

        Livewire::test(ShowCardBrands::class)
            ->call('confirmDelete', $brand->id)
            ->dispatch('delete-card-brand', id: $brand->id);

        $this->assertDatabaseMissing('financial_card_brands', ['id' => $brand->id]);
    }
}