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

    public function scopeByType($query, $isInflow)
    {
        return $query->where('is_inflow', $isInflow);
    }

    public function scopeInFlow($query)
    {
        return $query->where('is_inflow', true);
    }

    public function scopeOutFlow($query)
    {
        return $query->where('is_inflow', false);
    }

    public function scopeThisInterval($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    protected static function newFactory()
    {
        return CompanyCashTransactionsFactory::new();
    }
}
