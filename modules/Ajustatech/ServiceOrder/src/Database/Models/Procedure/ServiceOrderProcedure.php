<?php

namespace Ajustatech\ServiceOrder\Database\Models\Procedure;

use Ajustatech\ServiceOrder\Database\Factories\Procedure\ServiceOrderProcedureFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrderProcedure extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_procedures';

    protected $fillable = [
        'name',
        'description',
        'value',
        'help_text',
        'help_image_url',
        'help_video_url',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return ServiceOrderProcedureFactory::new();
    }
}

