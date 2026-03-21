<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\SalesCash;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Livewire\SalesCashDailyManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesCashDailyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_user_can_open_and_close_daily_sales_cash(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $managerialCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Gerencial',
            'balance_amount' => 600,
            'is_online' => true,
            'is_active' => true,
            'is_managerial' => true,
        ]);

        Livewire::test(SalesCashDailyManagement::class)
            ->set('managerialCashId', $managerialCash->id)
            ->set('openingAmount', 100)
            ->call('openCash')
            ->assertHasNoErrors();

        Livewire::test(SalesCashDailyManagement::class)
            ->set('closingAmount', 100)
            ->call('closeCash')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales_cash_sessions', [
            'user_id' => $user->id,
            'status' => 'closed',
        ]);
    }
}

