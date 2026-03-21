<?php

namespace Ajustatech\Financial\Database\Seeders\Concerns;

use Ajustatech\Financial\Database\Models\CompanyCash;

trait EnsuresDefaultManagerialCash
{
    private const DEFAULT_MANAGERIAL_CASH_NAME = 'Caixa Repellendus';

    private function ensureDefaultManagerialCash(): CompanyCash
    {
        $cash = CompanyCash::query()
            ->where('cash_name', self::DEFAULT_MANAGERIAL_CASH_NAME)
            ->where('is_managerial', true)
            ->first();

        if (!$cash) {
            $cash = $this->createCashWithInitialBalance([
                'cash_name' => self::DEFAULT_MANAGERIAL_CASH_NAME,
                'description' => 'Caixa gerencial padrao dos seeds financeiros',
                'is_online' => true,
                'is_active' => true,
                'is_managerial' => true,
            ], 25000);
        }

        $this->ensureBalanceSnapshot($cash);

        return $cash;
    }

    private function ensureBalanceSnapshot(CompanyCash $cash): void
    {
        if ($cash->balances()->exists()) {
            return;
        }

        $totalInflows = $cash->transactions()->where('is_inflow', true)->count();
        $totalOutflows = $cash->transactions()->where('is_inflow', false)->count();
        $balance = (float) ($cash->calculateBalance() ?? 0);

        $cash->balances()->create([
            'total_inflows' => $totalInflows,
            'total_outflows' => $totalOutflows,
            'balance' => $balance,
        ]);
    }

    private function createCashWithInitialBalance(array $attributes, float $initialBalance): CompanyCash
    {
        $cash = CompanyCash::create($attributes);

        $cash->balances()->create([
            'total_inflows' => 1,
            'total_outflows' => 0,
            'balance' => $initialBalance,
        ]);

        return $cash;
    }
}
