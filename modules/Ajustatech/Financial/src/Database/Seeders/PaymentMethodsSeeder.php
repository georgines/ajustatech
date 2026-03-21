<?php

namespace Ajustatech\Financial\Database\Seeders;

use Ajustatech\Financial\Database\Models\FinancialCardBrand;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        (new FinancialCardBrandSeeder())->run();

        $defaults = [
            [
                'type' => FinancialPaymentMethod::TYPE_DINHEIRO,
                'name' => 'Dinheiro',
                'costs' => [
                    ['fixed_cost' => 0, 'percent_cost' => 0, 'brand' => null, 'installments' => null, 'receipt_channel' => null],
                ],
            ],
            [
                'type' => FinancialPaymentMethod::TYPE_PIX,
                'name' => 'Pix',
                'costs' => [
                    ['fixed_cost' => 0, 'percent_cost' => 0, 'brand' => null, 'installments' => null, 'receipt_channel' => null],
                ],
            ],
            [
                'type' => FinancialPaymentMethod::TYPE_CARTAO_DEBITO,
                'name' => 'Cartao Debito',
                'costs' => [
                    ['fixed_cost' => 0.39, 'percent_cost' => 1.99, 'brand' => 'Visa', 'installments' => 1, 'receipt_channel' => 'maquina'],
                    ['fixed_cost' => 0.39, 'percent_cost' => 1.99, 'brand' => 'Mastercard', 'installments' => 1, 'receipt_channel' => 'maquina'],
                ],
            ],
            [
                'type' => FinancialPaymentMethod::TYPE_CARTAO_CREDITO,
                'name' => 'Cartao Credito',
                'costs' => [
                    ['fixed_cost' => 0.49, 'percent_cost' => 3.49, 'brand' => 'Visa', 'installments' => 1, 'receipt_channel' => 'maquina'],
                    ['fixed_cost' => 0.49, 'percent_cost' => 3.49, 'brand' => 'Mastercard', 'installments' => 1, 'receipt_channel' => 'maquina'],
                    ['fixed_cost' => 0.49, 'percent_cost' => 4.29, 'brand' => 'Visa', 'installments' => 6, 'receipt_channel' => 'link'],
                ],
            ],
        ];

        foreach ($defaults as $data) {
            $method = FinancialPaymentMethod::updateOrCreate(
                [
                    'type' => $data['type'],
                    'name' => $data['name'],
                ],
                ['is_active' => true]
            );

            $method->costs()->delete();
            $method->costs()->createMany($data['costs']);
        }

        FinancialCardBrand::query()->whereIn('name', ['Visa', 'Mastercard', 'Elo', 'Hipercard', 'American Express'])->update(['is_active' => true]);
    }
}
