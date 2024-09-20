<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\CompanyCashBalancesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompanyCashBalances extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'total_inflows',
        'total_outflows',
        'balance'
    ];

    public function companyCash()
    {
        return $this->belongsTo(CompanyCash::class);
    }

    protected static function newFactory()
    {
        return CompanyCashBalancesFactory::new();
    }
}
