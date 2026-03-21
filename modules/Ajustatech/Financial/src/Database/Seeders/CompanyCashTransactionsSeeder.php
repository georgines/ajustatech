<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Ajustatech\Financial\Database\Models\CompanyCash;
use Illuminate\Database\Seeder;

class CompanyCashTransactionsSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $this->ensureDefaultManagerialCash();

        $cashes = CompanyCash::query()->get();
        if ($cashes->isEmpty()) {
            (new CompanyCashSeeder())->run();
            $cashes = CompanyCash::query()->get();
        }

        foreach (range(1, 15) as $_) {
            /** @var CompanyCash $cash */
            $cash = $cashes->random();
            $amount = fake()->randomFloat(2, 10, 500);

            if ((bool) random_int(0, 1)) {
                $cash->registerInflow($amount, 'Entrada seed');
                continue;
            }

            if ($cash->hasSufficientBalance($amount)) {
                $cash->registerOutflow($amount, 'Saida seed');
            }
        }
    }
}
