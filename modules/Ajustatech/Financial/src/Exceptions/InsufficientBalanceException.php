<?php

namespace Ajustatech\Financial\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    private float $requiredAmount;
    private float $availableBalance;

    public function __construct(float $requiredAmount, float $availableBalance)
    {
        $this->requiredAmount = $requiredAmount;
        $this->availableBalance = $availableBalance;

        $message = "Saldo insuficiente. Necessário: {$this->requiredAmount}, Disponível: {$this->availableBalance}.";
        parent::__construct($message);
    }

    public function getMissingAmount(): float
    {
        return $this->requiredAmount - $this->availableBalance;
    }
}
