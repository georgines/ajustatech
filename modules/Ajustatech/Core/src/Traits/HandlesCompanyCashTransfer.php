<?php

namespace Ajustatech\Core\Traits;

use Ajustatech\Core\Rules\DifferentValueRule;

trait HandlesCompanyCashTransfer
{
    protected function transferValidationRules(string $originCashId): array
    {
        return [
            'destinationCashId' => ['required', 'exists:company_cashes,id', new DifferentValueRule($originCashId)],
            'transferAmount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    protected function transferValidationAttributes(): array
    {
        return [
            'destinationCashId' => 'caixa de destino',
            'transferAmount' => 'valor da transferencia',
        ];
    }

    protected function resetTransferForm(): void
    {
        $this->destinationCashId = '';
        $this->transferAmount = null;
    }
}

