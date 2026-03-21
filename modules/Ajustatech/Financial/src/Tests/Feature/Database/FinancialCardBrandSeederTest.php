<?php

namespace Ajustatech\Financial\Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialCardBrandSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_card_brands_seed_command_should_create_traditional_brands(): void
    {
        $this->artisan('module:seed-financial-card-brands')->assertExitCode(0);

        $this->assertDatabaseHas('financial_card_brands', ['name' => 'Visa']);
        $this->assertDatabaseHas('financial_card_brands', ['name' => 'Mastercard']);
        $this->assertDatabaseHas('financial_card_brands', ['name' => 'Elo']);
        $this->assertDatabaseHas('financial_card_brands', ['name' => 'Hipercard']);
        $this->assertDatabaseHas('financial_card_brands', ['name' => 'American Express']);
    }
}