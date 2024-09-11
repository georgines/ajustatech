<?php

namespace Ajustatech\Financial\Database\Models;

use Ajustatech\Financial\Database\Factories\FinancialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        
    ];


    protected static function newFactory(){
        return FinancialFactory::new();
    }
}
