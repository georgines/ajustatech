<?php

namespace Ajustatech\Financial\Commands;

use Illuminate\Console\Command;

class SeedPaymentMethodsCommand extends Command
{
    protected $signature = 'module:seed-financial-payment-methods';

    protected $description = 'Seeds the database with financial payment methods data';

    public function handle(): void
    {
        $this->call('db:seed', [
            '--class' => 'Ajustatech\\Financial\\Database\\Seeders\\PaymentMethodsSeeder',
        ]);
    }
}
