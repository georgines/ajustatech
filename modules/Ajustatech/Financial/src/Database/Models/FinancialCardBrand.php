<?php

namespace Ajustatech\Financial\Database\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FinancialCardBrand extends Model
{
    use HasUuids;

    protected $table = 'financial_card_brands';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}