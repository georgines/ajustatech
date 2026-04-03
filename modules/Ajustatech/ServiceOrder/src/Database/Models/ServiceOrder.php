<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    private const COUNT_CACHE_KEY = 'service_order:orders:count_all';
    private const COUNT_CACHE_TTL_SECONDS = 60;

    protected $table = 'service_orders';

    protected $fillable = [
        'order_number',
        'equipment_type_id',
        'customer_id',
        'customer_name',
        'equipment_name',
        'brand',
        'model',
        'serial_number',
        'entry_date',
        'reported_issue',
        'status',
        'equipment_type_snapshot',
        'fields_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'order_number' => 'integer',
            'entry_date' => 'date',
            'equipment_type_snapshot' => 'array',
            'fields_snapshot' => 'array',
        ];
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ServiceOrderFieldValue::class, 'service_order_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceOrderAttachment::class, 'service_order_id');
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceOrderServiceItem::class, 'service_order_id');
    }

    public function analysisServices(): HasMany
    {
        return $this->hasMany(ServiceOrderAnalysisService::class, 'service_order_id');
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest();
    }

    public static function countAll(): int
    {
        return (int) Cache::remember(
            static::COUNT_CACHE_KEY,
            static::COUNT_CACHE_TTL_SECONDS,
            fn () => static::query()->count()
        );
    }

    public static function createFromPayload(array $payload): self
    {
        if (!isset($payload['order_number']) || (int) $payload['order_number'] <= 0) {
            $payload['order_number'] = static::nextOrderNumberPreview();
        }

        return static::query()->create($payload);
    }

    public static function nextOrderNumberPreview(): int
    {
        return ((int) static::query()->max('order_number')) + 1;
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function findWithFieldValuesOrFail(string $id): self
    {
        return static::query()->with('fieldValues')->findOrFail($id);
    }

    public static function findForEditOrFail(string $id): self
    {
        return static::query()
            ->with(['fieldValues', 'serviceItems', 'analysisServices'])
            ->findOrFail($id);
    }

    public static function findWithAllRelationsOrFail(string $id): self
    {
        return static::query()
            ->with(['fieldValues', 'attachments', 'serviceItems', 'analysisServices'])
            ->findOrFail($id);
    }

    public function freshWithAllRelations(): self
    {
        return $this->fresh(['fieldValues', 'attachments', 'serviceItems', 'analysisServices']);
    }

    public function loadMissingAllRelations(): self
    {
        return $this->loadMissing('fieldValues', 'attachments', 'serviceItems', 'analysisServices');
    }

    public static function getLatestListingWithServiceItems(int $limit = 100): Collection
    {
        return static::query()
            ->withCount('analysisServices')
            ->latestFirst()
            ->limit($limit)
            ->get();
    }

    public function countAttachmentsBySlug(string $slug): int
    {
        return $this->attachments()->where('field_slug', $slug)->count();
    }

    public function deleteAllServiceItems(): void
    {
        $this->serviceItems()->delete();
    }

    public function createServiceItem(array $payload): ServiceOrderServiceItem
    {
        return $this->serviceItems()->create($payload);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'canceled']);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (!$model->order_number) {
                $model->order_number = static::nextOrderNumberPreview();
            }
        });

        static::created(fn () => Cache::forget(static::COUNT_CACHE_KEY));
        static::deleted(fn () => Cache::forget(static::COUNT_CACHE_KEY));
    }

    public function getDocumentFieldsWithValues(): array
    {
        $valuesBySlug = $this->fieldValues->keyBy('field_slug');

        return collect($this->fields_snapshot)
            ->filter(fn (array $field) => ($field['field_type'] ?? null) === 'document')
            ->sortBy('sort_order')
            ->values()
            ->map(function (array $field) use ($valuesBySlug) {
                $slug = (string) ($field['slug'] ?? '');
                $value = $valuesBySlug->get($slug);

                return [
                    'name' => $field['name'] ?? $slug,
                    'slug' => $slug,
                    'value' => $value?->value_text,
                ];
            })
            ->all();
    }

    protected static function newFactory()
    {
        return ServiceOrderFactory::new();
    }
}
