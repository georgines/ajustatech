<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Seeders\Concerns\EnsuresDefaultManagerialCash;
use Illuminate\Database\Seeder;

class CompanyCashSeeder extends Seeder
{
    use EnsuresDefaultManagerialCash;

    public function run(): void
    {
        $this->ensureDefaultManagerialCash();

        for ($i = 1; $i <= 6; $i++) {
            $this->createCashWithInitialBalance([
                'cash_name' => "Caixa Operacional {$i}",
                'description' => "Caixa operacional seed {$i}",
                'is_online' => (bool) random_int(0, 1),
                'is_active' => true,
                'is_managerial' => false,
            ], fake()->randomFloat(2, 800, 5000));
        }
    }
}
