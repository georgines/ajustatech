<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_bases';

    protected $fillable = [

    ];


    protected static function newFactory(){
        return ServiceOrderFactory::new();
    }
}
