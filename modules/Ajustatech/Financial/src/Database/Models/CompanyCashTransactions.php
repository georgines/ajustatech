<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\CompanyCashTransactionsFactory;
use Ajustatech\Financial\Database\Filters\HasDateFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompanyCashTransactions extends Model
{
    use HasFactory;
    use HasUuids;
    use HasDateFilter;

    protected $fillable = [
        'company_cash_id',
        'company_cash_name',
        'amount',
        'description',
        'hash',
        'category',
        'is_inflow'
    ];

    public function companyCash()
    {
        return $this->belongsTo(CompanyCash::class);
    }

    public function scopeByType($query, $isCredit)
    {
        return $query->where('is_credit', $isCredit);
    }

    public function scopeIsCredit($query)
    {
        return $query->where('is_credit', true);
    }

    public function scopeIsDebit($query)
    {
        return $query->where('is_credit', false);
    }

    protected static function newFactory()
    {
        return CompanyCashTransactionsFactory::new();
    }
}
