<?php

namespace Ajustatech\Financial\Services;

interface CompanyCashTransactionsServiceInterface
{
    public function getAllTransactionsBetween($companyCashId, $startDate, $endDate, $offset, $limit);
}
