<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\FinancialPayable;
use Illuminate\Database\Seeder;

class FinancialPayablesSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $managerialCash = $this->ensureDefaultManagerialCash();

        FinancialPayable::factory()->count(8)->create([
            'company_cash_id' => $managerialCash->id,
            'status' => 'pending',
            'cash_flow_status' => 'pending',
            'settled_at' => null,
        ]);

        FinancialPayable::factory()->paid()->count(4)->create([
            'company_cash_id' => $managerialCash->id,
        ]);
    }
}
