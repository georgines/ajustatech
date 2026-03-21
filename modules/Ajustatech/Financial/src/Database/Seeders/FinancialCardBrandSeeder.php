<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Illuminate\Database\Seeder;

class FinancialCardBrandSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = ['Visa', 'Mastercard', 'Elo', 'Hipercard', 'American Express'];

        foreach ($defaults as $name) {
            FinancialCardBrand::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}