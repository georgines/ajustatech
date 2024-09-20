<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;
use Exception;
use InvalidArgumentException;

class CompanyCashService
{
    protected CompanyCash $cash;
    protected $currentCash;

    public function __construct(CompanyCash $cash)
    {
        $this->cash = $cash;
    }

    public function createCash(string $name)
    {
        $this->cash->create(['cash_name' => $name]);
    }

    public function getAllCashs()
    {
        return $this->cash->all();
    }

    public function getCash(string $id)
    {

        $this->currentCash = $this->cash->find($id);

        if (!$this->currentCash) {
            throw new Exception("Caixa atual não foi encontrado.");
        }
        return $this;
    }

    public function deposit(float $amount, string $description = null): void
    {
        $this->currentCash->registerInflow($amount, $description);
        // $this->validateAmount($amount);
        // $this->cash->registerTransaction($amount, true, $description);
        // $this->updateBalance($amount, true);
    }

    public function withdraw(float $amount, string $description = null): void
    {
        $this->validateAmount($amount);
        $this->hasSufficientBalance($amount);
        $this->cash->registerTransaction($amount, false, $description);
        $this->updateBalance($amount, false);
    }

    public function transfer(string $destinationCashId, float $amount, string $description = null): void
    {
        $this->validateAmount($amount);
        $this->hasSufficientBalance($amount);
        $this->withdraw($amount, $description);

        $destinationCashService = $this->cash->find($destinationCashId);
        $destinationCashService->deposit($amount, $description);
    }

    public function pay(float $amount, string $description = null): void
    {
        $this->withdraw($amount, $description);
    }

    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("O valor deve ser positivo.");
        }
    }

    private function hasSufficientBalance(float $amount): void
    {
        $balance = $this->cash->getLatestBalance();
        if ($balance->balance < $amount) {
            throw new Exception("Saldo insuficiente.");
        }
    }

    private function updateBalance(float $amount, bool $isInflow): void
    {
        $balance = $this->cash->getLatestBalance();
        if ($isInflow) {
            $balance->total_inflows += $amount;
            $balance->balance += $amount;
        } else {
            $balance->total_outflows += $amount;
            $balance->balance -= $amount;
        }
        $balance->save();
    }
}
