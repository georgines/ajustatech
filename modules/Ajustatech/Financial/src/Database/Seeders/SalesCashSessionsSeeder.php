<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Ajustatech\Financial\Database\Models\SalesCashSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesCashSessionsSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $sourceCash = $this->ensureDefaultManagerialCash();

        $destinationCash = CompanyCash::query()
            ->where('is_managerial', true)
            ->where('id', '!=', $sourceCash->id)
            ->first();

        if (!$destinationCash) {
            $destinationCash = $this->createCashWithInitialBalance([
                'cash_name' => 'Caixa Retorno Vendas',
                'description' => 'Destino para sessoes de caixa de vendas',
                'is_online' => true,
                'is_active' => true,
                'is_managerial' => true,
            ], 5000);
        }

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            SalesCashSession::factory()->create([
                'user_id' => $user->id,
                'source_company_cash_id' => $sourceCash->id,
                'destination_company_cash_id' => $destinationCash->id,
                'status' => 'open',
            ]);

            SalesCashSession::factory()->closed()->create([
                'user_id' => $user->id,
                'source_company_cash_id' => $sourceCash->id,
                'destination_company_cash_id' => $destinationCash->id,
            ]);
        }
    }
}
