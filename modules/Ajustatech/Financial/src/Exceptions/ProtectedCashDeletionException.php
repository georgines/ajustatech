<?php

namespace Ajustatech\Financial\Exceptions;

use RuntimeException;

class ProtectedCashDeletionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(trans('financial::messages.managerial_cash_cannot_be_deleted'));
    }
}

