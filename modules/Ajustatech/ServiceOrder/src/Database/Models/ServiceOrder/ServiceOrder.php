<?php

namespace Ajustatech\ServiceOrder\Database\Models\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Factories\ServiceOrder\ServiceOrderFactory;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderSetting;
use Ajustatech\Settings\Database\Models\ServiceOrder\ServiceOrderStatusFlow;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_bases';

    protected $fillable = [
        'order_number',
        'status_flow_id',
        'customer_id',
        'equipment_type_id',
        'selected_document_id',
        'customer_snapshot_json',
        'equipment_brand',
        'equipment_model',
        'equipment_serial_number',
        'opened_at',
        'finished_at',
    ];

    protected $casts = [
        'order_number' => 'integer',
        'customer_snapshot_json' => 'array',
        'opened_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $serviceOrder): void {
            if (empty($serviceOrder->status_flow_id)) {
                $serviceOrder->status_flow_id = ServiceOrderStatusFlow::defaultInitialId();
            }

            if (empty($serviceOrder->order_number)) {
                $serviceOrder->order_number = static::nextOrderNumber();
            }

            if (empty($serviceOrder->opened_at)) {
                $serviceOrder->opened_at = now();
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentType::class, 'equipment_type_id');
    }

    public function selectedDocument(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentTypeDocument::class, 'selected_document_id');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceOrderServiceItem::class, 'service_order_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ServiceOrderEquipmentFieldValue::class, 'service_order_id')
            ->orderBy('created_at');
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

    public static function listForIndex(
        string $search = '',
        ?string $statusFlowId = null,
        ?string $openedFrom = null,
        ?string $openedTo = null,
        int $limitPerPage = 10
    ) {
        return static::query()
            ->with([
                'statusFlow:id,name,code',
                'customer:id,name,cpf_cnpj',
                'equipmentType:id,name',
                'equipmentType.documents:id,equipment_type_id,document_type,title,template_content,path,disk,original_name',
                'selectedDocument:id,title,document_type',
            ])
            ->search($search)
            ->forStatus($statusFlowId)
            ->betweenOpenedDates($openedFrom, $openedTo)
            ->orderByDesc('order_number')
            ->paginate($limitPerPage);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = trim($search);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder->where('order_number', 'like', "%{$term}%")
                ->orWhere('equipment_brand', 'like', "%{$term}%")
                ->orWhere('equipment_model', 'like', "%{$term}%")
                ->orWhere('equipment_serial_number', 'like', "%{$term}%")
                ->orWhereHas('customer', function (Builder $customerBuilder) use ($term): void {
                    $customerBuilder->where('name', 'like', "%{$term}%")
                        ->orWhere('cpf_cnpj', 'like', "%{$term}%");
                });
        });
    }

    public function scopeForStatus(Builder $query, ?string $statusFlowId): Builder
    {
        if (blank($statusFlowId)) {
            return $query;
        }

        return $query->where('status_flow_id', $statusFlowId);
    }

    public function scopeBetweenOpenedDates(Builder $query, ?string $openedFrom, ?string $openedTo): Builder
    {
        if (filled($openedFrom)) {
            $query->whereDate('opened_at', '>=', $openedFrom);
        }

        if (filled($openedTo)) {
            $query->whereDate('opened_at', '<=', $openedTo);
        }

        return $query;
    }

    public function businessDaysSinceCreation(array $workingDays, array $holidays = []): int
    {
        $openedAt = $this->opened_at?->toImmutable() ?? CarbonImmutable::instance($this->created_at);
        $today = now()->toImmutable();

        if ($openedAt->greaterThan($today)) {
            return 0;
        }

        $workingDaysMap = collect($workingDays)
            ->map(fn (string $day) => strtolower($day))
            ->flip();

        $holidayMap = collect($holidays)
            ->filter(fn ($date) => is_string($date))
            ->flip();

        $cursor = $openedAt->startOfDay();
        $end = $today->startOfDay();
        $businessDays = 0;

        while ($cursor->lessThanOrEqualTo($end)) {
            $dayName = strtolower($cursor->englishDayOfWeek);
            $dayDate = $cursor->format('Y-m-d');

            if ($workingDaysMap->has($dayName) && ! $holidayMap->has($dayDate)) {
                $businessDays++;
            }

            $cursor = $cursor->addDay();
        }

        return max(0, $businessDays - 1);
    }
}
