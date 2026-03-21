<?php

namespace Ajustatech\Financial\Tests\Feature\Livewire\CompanyCashTransactions;

use Ajustatech\Financial\Livewire\ShowCompanyCashTransactions;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShowCompanyCashTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully()
    {
        $cash = CompanyCash::createNew([
            'cash_name' => 'Caixa Origem',
            'balance_amount' => 100,
            'is_online' => true,
            'is_active' => true,
        ]);

        Livewire::test(ShowCompanyCashTransactions::class, ['id' => $cash->id])
            ->assertStatus(200);
    }

    public function test_can_transfer_to_another_cash()
    {
        $originCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Origem',
            'balance_amount' => 500,
            'is_online' => true,
            'is_active' => true,
        ]);

        $destinationCash = CompanyCash::createNew([
            'cash_name' => 'Caixa Destino',
            'balance_amount' => 100,
            'is_online' => true,
            'is_active' => true,
        ]);

        Livewire::test(ShowCompanyCashTransactions::class, ['id' => $originCash->id])
            ->set('destinationCashId', $destinationCash->id)
            ->set('transferAmount', 150)
            ->call('transferToAnotherCash')
            ->assertHasNoErrors();

        $this->assertEquals(350.0, (float) $originCash->fresh()->calculateBalance());
        $this->assertEquals(250.0, (float) $destinationCash->fresh()->calculateBalance());
    }

    public function test_cannot_transfer_to_same_cash()
    {
        $cash = CompanyCash::createNew([
            'cash_name' => 'Caixa Unico',
            'balance_amount' => 500,
            'is_online' => true,
            'is_active' => true,
        ]);

        Livewire::test(ShowCompanyCashTransactions::class, ['id' => $cash->id])
            ->set('destinationCashId', $cash->id)
            ->set('transferAmount', 150)
            ->call('transferToAnotherCash')
            ->assertHasErrors(['destinationCashId']);
    }
}
