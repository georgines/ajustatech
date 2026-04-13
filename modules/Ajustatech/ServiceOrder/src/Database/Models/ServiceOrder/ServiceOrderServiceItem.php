<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderServiceItemFactory;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderServiceItem extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_service_items';

    protected $fillable = [
        'service_order_id',
        'procedure_id',
        'item_name',
        'item_notes',
        'unit_value',
        'discount_value',
        'total_value',
        'sort_order',
    ];

    protected $casts = [
        'unit_value' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderServiceItemFactory::new();
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderProcedure::class, 'procedure_id');
    }

    public static function syncForServiceOrder(ServiceOrder $serviceOrder, array $serviceItems): void
    {
        static::query()
            ->where('service_order_id', $serviceOrder->id)
            ->delete();

        $rows = collect($serviceItems)
            ->map(function (array $serviceItem, int $index) use ($serviceOrder) {
                $unitValue = max(0, round((float) ($serviceItem['unit_value'] ?? 0), 2));
                $discountValue = min(max(0, round((float) ($serviceItem['discount_value'] ?? 0), 2)), $unitValue);
                $itemName = trim((string) ($serviceItem['item_name'] ?? ''));

                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $serviceOrder->id,
                    'procedure_id' => blank($serviceItem['procedure_id'] ?? null) ? null : (string) $serviceItem['procedure_id'],
                    'item_name' => $itemName,
                    'item_notes' => blank($serviceItem['item_notes'] ?? null) ? null : trim((string) $serviceItem['item_notes']),
                    'unit_value' => $unitValue,
                    'discount_value' => $discountValue,
                    'total_value' => max(0, $unitValue - $discountValue),
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->filter(fn (array $item) => $item['item_name'] !== '')
            ->values()
            ->all();

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }

    public static function duplicateForServiceOrder(ServiceOrder $source, ServiceOrder $target): void
    {
        $items = $source->relationLoaded('serviceItems') ? $source->serviceItems : $source->serviceItems()->get();

        $rows = $items
            ->map(function (self $item) use ($target) {
                return [
                    'id' => (string) Str::uuid(),
                    'service_order_id' => $target->id,
                    'procedure_id' => $item->procedure_id,
                    'item_name' => $item->item_name,
                    'item_notes' => $item->item_notes,
                    'unit_value' => $item->unit_value,
                    'discount_value' => $item->discount_value,
                    'total_value' => $item->total_value,
                    'sort_order' => $item->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        if (! empty($rows)) {
            static::query()->insert($rows);
        }
    }
}
