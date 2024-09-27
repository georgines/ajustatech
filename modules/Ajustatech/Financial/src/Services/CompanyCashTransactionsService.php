<?php

namespace Ajustatech\Financial\Services;

use Ajustatech\Financial\Database\Models\CompanyCash;

class CompanyCashTransactionsService implements CompanyCashTransactionsServiceInterface
{
    protected $cash;

    public function __construct(CompanyCash $cash)
    {
        $this->cash = $cash;
    }

    public function getAllTransactionsBetween($companyCashId, $startDate, $endDate, $offset, $limit)
    {
        $cash = CompanyCash::findOrFail($companyCashId);
        $query = $cash->transactions()->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->skip($offset)->take($limit)->get()->toArray();
    }
}
