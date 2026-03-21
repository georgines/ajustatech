<?php

namespace Ajustatech\Financial\Database\Models;

use App\Models\User;
use Ajustatech\Financial\Database\Factories\SalesCashSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCashSession extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'sales_cash_sessions';

    protected $fillable = [
        'user_id',
        'source_company_cash_id',
        'destination_company_cash_id',
        'opening_payment_method_type',
        'closing_payment_method_type',
        'business_date',
        'opening_amount',
        'closing_amount',
        'opened_at',
        'closed_at',
        'status',
        'outflow_status',
        'inflow_status',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'float',
            'closing_amount' => 'float',
            'business_date' => 'date',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceCash(): BelongsTo
    {
        return $this->belongsTo(CompanyCash::class, 'source_company_cash_id');
    }

    public function destinationCash(): BelongsTo
    {
        return $this->belongsTo(CompanyCash::class, 'destination_company_cash_id');
    }

    protected static function newFactory()
    {
        return SalesCashSessionFactory::new();
    }
}
