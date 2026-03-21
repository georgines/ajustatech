<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Database\Seeder;

class CompanyCashBalancesSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $this->ensureDefaultManagerialCash();

        CompanyCash::query()->each(function (CompanyCash $cash): void {
            if ($cash->balances()->exists()) {
                return;
            }

            $cash->balances()->create([
                'total_inflows' => 0,
                'total_outflows' => 0,
                'balance' => (float) ($cash->calculateBalance() ?? 0),
            ]);
        });
    }
}
