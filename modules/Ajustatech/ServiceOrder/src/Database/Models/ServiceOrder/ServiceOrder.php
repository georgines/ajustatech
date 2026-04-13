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
use Illuminate\Support\Facades\DB;

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

    public function toManagementForm(): array
    {
        return [
            'customer_id' => (string) $this->customer_id,
            'equipment_type_id' => (string) ($this->equipment_type_id ?? ''),
            'selected_document_id' => (string) ($this->selected_document_id ?? ''),
            'equipment_brand' => (string) ($this->equipment_brand ?? ''),
            'equipment_model' => (string) ($this->equipment_model ?? ''),
            'equipment_serial_number' => (string) ($this->equipment_serial_number ?? ''),
        ];
    }

    public function toManagementServiceItems(): array
    {
        $items = $this->relationLoaded('serviceItems') ? $this->serviceItems : $this->serviceItems()->get();

        return $items
            ->map(fn (ServiceOrderServiceItem $serviceItem) => [
                'procedure_id' => (string) ($serviceItem->procedure_id ?? ''),
                'item_name' => (string) $serviceItem->item_name,
                'item_notes' => (string) ($serviceItem->item_notes ?? ''),
                'unit_value' => number_format((float) $serviceItem->unit_value, 2, '.', ''),
                'discount_value' => number_format((float) $serviceItem->discount_value, 2, '.', ''),
                'total_value' => number_format((float) $serviceItem->total_value, 2, '.', ''),
            ])
            ->values()
            ->all();
    }

    public function toManagementDynamicFields(): array
    {
        $fieldValues = $this->relationLoaded('fieldValues') ? $this->fieldValues : $this->fieldValues()->get();

        return $fieldValues
            ->map(fn (ServiceOrderEquipmentFieldValue $fieldValue) => [
                'equipment_type_field_id' => (string) ($fieldValue->equipment_type_field_id ?? ''),
                'field_type' => (string) ($fieldValue->field_type ?? ''),
                'field_label' => (string) $fieldValue->field_label,
                'field_placeholder' => (string) ($fieldValue->field_placeholder ?? ''),
                'is_required' => (bool) $fieldValue->is_required,
                'value_text' => (string) ($fieldValue->value_text ?? ''),
            ])
            ->values()
            ->all();
    }

    public function toManagementMeta(): array
    {
        $statusFlow = $this->relationLoaded('statusFlow') ? $this->statusFlow : $this->statusFlow()->first();

        return [
            'id' => (string) $this->id,
            'order_number' => $this->order_number,
            'opened_at_display' => $this->opened_at?->format('d/m/Y H:i') ?? '-',
            'finished_at_display' => $this->finished_at?->format('d/m/Y H:i') ?? '-',
            'status_flow' => [
                'id' => (string) ($statusFlow?->id ?? ''),
                'code' => (string) ($statusFlow?->code ?? ''),
                'name' => (string) ($statusFlow?->name ?? ''),
            ],
        ];
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
    )
    {
        return static::query()
            ->with([
                'statusFlow:id,name,code',
                'customer:id,name,cpf_cnpj',
            ])
            ->search($search)
            ->forStatus($statusFlowId)
            ->betweenOpenedDates($openedFrom, $openedTo)
            ->orderByDesc('order_number')
            ->paginate($limitPerPage);
    }

    public static function findDetailedOrFail(string $id): self
    {
        return static::query()
            ->with([
                'statusFlow:id,name,code',
                'customer:id,name,person,cpf_cnpj,cellphone,phone,email,zip_code,address,number,neighborhood,city,state,status',
                'equipmentType:id,name',
                'equipmentType.documents:id,equipment_type_id,document_type,title,template_content,path,disk,original_name,variables_json',
                'equipmentType.fields:id,equipment_type_id,field_type,label,placeholder,is_required,default_text',
                'selectedDocument:id,title,document_type,template_content,path,disk,original_name,variables_json',
                'serviceItems:id,service_order_id,procedure_id,item_name,item_notes,unit_value,discount_value,total_value,sort_order',
                'fieldValues:id,service_order_id,equipment_type_field_id,field_type,field_label,field_placeholder,is_required,value_text',
            ])
            ->findOrFail($id);
    }

    public static function createFromPayload(array $payload): self
    {
        return DB::transaction(function () use ($payload) {
            $customer = Customer::findForServiceOrderOrFail((string) $payload['customer_id']);

            $serviceOrder = static::query()->create([
                'customer_id' => $customer->id,
                'equipment_type_id' => blank($payload['equipment_type_id'] ?? null) ? null : (string) $payload['equipment_type_id'],
                'selected_document_id' => blank($payload['selected_document_id'] ?? null) ? null : (string) $payload['selected_document_id'],
                'equipment_brand' => blank($payload['equipment_brand'] ?? null) ? null : trim((string) $payload['equipment_brand']),
                'equipment_model' => blank($payload['equipment_model'] ?? null) ? null : trim((string) $payload['equipment_model']),
                'equipment_serial_number' => blank($payload['equipment_serial_number'] ?? null) ? null : trim((string) $payload['equipment_serial_number']),
                'customer_snapshot_json' => $customer->toServiceOrderSnapshot(),
                'opened_at' => now(),
            ]);

            $serviceOrder->syncFieldValues($payload['dynamic_fields'] ?? []);
            $serviceOrder->syncServiceItems($payload['service_items'] ?? []);

            return static::findDetailedOrFail($serviceOrder->id);
        });
    }

    public static function updateFromPayloadById(string $id, array $payload): self
    {
        return DB::transaction(function () use ($id, $payload) {
            $serviceOrder = static::findDetailedOrFail($id);

            $serviceOrder->update([
                'equipment_type_id' => blank($payload['equipment_type_id'] ?? null) ? null : (string) $payload['equipment_type_id'],
                'selected_document_id' => blank($payload['selected_document_id'] ?? null) ? null : (string) $payload['selected_document_id'],
                'equipment_brand' => blank($payload['equipment_brand'] ?? null) ? null : trim((string) $payload['equipment_brand']),
                'equipment_model' => blank($payload['equipment_model'] ?? null) ? null : trim((string) $payload['equipment_model']),
                'equipment_serial_number' => blank($payload['equipment_serial_number'] ?? null) ? null : trim((string) $payload['equipment_serial_number']),
            ]);

            $serviceOrder->syncFieldValues($payload['dynamic_fields'] ?? []);
            $serviceOrder->syncServiceItems($payload['service_items'] ?? []);

            return static::findDetailedOrFail($serviceOrder->id);
        });
    }

    public static function refreshCustomerSnapshotById(string $id): self
    {
        return DB::transaction(function () use ($id) {
            $serviceOrder = static::query()->with('customer')->findOrFail($id);

            if ($serviceOrder->customer) {
                $serviceOrder->update([
                    'customer_snapshot_json' => $serviceOrder->customer->toServiceOrderSnapshot(),
                ]);
            }

            return static::findDetailedOrFail($serviceOrder->id);
        });
    }

    public static function deleteById(string $id): void
    {
        static::query()->findOrFail($id)->delete();
    }

    public function duplicateWithRelations(): self
    {
        return DB::transaction(function () {
            $source = static::findDetailedOrFail($this->id);

            $clone = static::query()->create([
                'customer_id' => $source->customer_id,
                'status_flow_id' => $source->status_flow_id,
                'equipment_type_id' => $source->equipment_type_id,
                'selected_document_id' => $source->selected_document_id,
                'customer_snapshot_json' => $source->customer_snapshot_json,
                'equipment_brand' => $source->equipment_brand,
                'equipment_model' => $source->equipment_model,
                'equipment_serial_number' => $source->equipment_serial_number,
                'opened_at' => now(),
                'finished_at' => null,
            ]);

            ServiceOrderEquipmentFieldValue::duplicateForServiceOrder($source, $clone);
            ServiceOrderServiceItem::duplicateForServiceOrder($source, $clone);

            return static::findDetailedOrFail($clone->id);
        });
    }

    public function syncFieldValues(array $dynamicFields): void
    {
        ServiceOrderEquipmentFieldValue::syncForServiceOrder($this, $dynamicFields);
    }

    public function syncServiceItems(array $serviceItems): void
    {
        ServiceOrderServiceItem::syncForServiceOrder($this, $serviceItems);
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

    public function businessDaysSinceCreation(?array $workingDays = null, ?array $holidays = null): int
    {
        $openedAt = $this->opened_at?->toImmutable() ?? CarbonImmutable::instance($this->created_at);
        $today = now()->toImmutable();

        if ($openedAt->greaterThan($today)) {
            return 0;
        }

        $workingDaysMap = collect($workingDays ?? $this->defaultWorkingDays())
            ->map(fn (string $day) => strtolower($day))
            ->flip();

        $holidayMap = collect($holidays ?? [])
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

    private function defaultWorkingDays(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }
}
