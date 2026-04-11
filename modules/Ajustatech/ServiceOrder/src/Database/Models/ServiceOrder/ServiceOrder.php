<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderFactory;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_bases';

    protected $fillable = [
        'order_number',
        'status_flow_id',
    ];

    protected $casts = [
        'order_number' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $serviceOrder) {
            if (empty($serviceOrder->status_flow_id)) {
                $serviceOrder->status_flow_id = ServiceOrderStatusFlow::defaultInitialId();
            }

            if (empty($serviceOrder->order_number)) {
                $serviceOrder->order_number = static::nextOrderNumber();
            }
        });
    }

    protected static function newFactory()
    {
        return ServiceOrderFactory::new();
    }

    public function statusFlow(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderStatusFlow::class, 'status_flow_id');
    }

    public static function nextOrderNumber(): int
    {
        $maxOrderNumber = (int) static::query()->max('order_number');
        $initialOrderNumber = ServiceOrderSetting::singleton()->initial_order_number;

        if ($maxOrderNumber <= 0) {
            return (int) $initialOrderNumber;
        }

        return max($maxOrderNumber + 1, (int) $initialOrderNumber);
    }
}
