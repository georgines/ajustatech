<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\FinancialReceivable;
use Illuminate\Database\Seeder;

class FinancialReceivablesSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $managerialCash = $this->ensureDefaultManagerialCash();

        FinancialReceivable::factory()->count(8)->create([
            'company_cash_id' => $managerialCash->id,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
            'settled_at' => null,
        ]);

        FinancialReceivable::factory()->received()->count(4)->create([
            'company_cash_id' => $managerialCash->id,
        ]);
    }
}
