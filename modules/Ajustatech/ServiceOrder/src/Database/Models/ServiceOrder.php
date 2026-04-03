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

class ServiceOrder extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_orders';

    protected $fillable = [
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

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest();
    }

    public static function countAll(): int
    {
        return static::query()->count();
    }

    public static function createFromPayload(array $payload): self
    {
        return static::query()->create($payload);
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
            ->with(['fieldValues', 'serviceItems'])
            ->findOrFail($id);
    }

    public static function findWithAllRelationsOrFail(string $id): self
    {
        return static::query()
            ->with(['fieldValues', 'attachments', 'serviceItems'])
            ->findOrFail($id);
    }

    public function freshWithAllRelations(): self
    {
        return $this->fresh(['fieldValues', 'attachments', 'serviceItems']);
    }

    public function loadMissingAllRelations(): self
    {
        return $this->loadMissing('fieldValues', 'attachments', 'serviceItems');
    }

    public static function getLatestListingWithServiceItems(int $limit = 100): Collection
    {
        return static::query()
            ->with('serviceItems')
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
