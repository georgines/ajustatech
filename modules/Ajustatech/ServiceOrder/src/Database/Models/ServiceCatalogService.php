<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\ServiceCatalogServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ServiceCatalogService extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_catalog_services';

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'is_active',
        'is_reusable',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_reusable' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(ServiceOrderServiceItem::class, 'service_catalog_service_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ServiceCatalogServiceStep::class, 'service_catalog_service_id')
            ->orderBy('sort_order');
    }

    public function activeSteps(): HasMany
    {
        return $this->steps()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeReusable(Builder $query): Builder
    {
        return $query->where('is_reusable', true);
    }

    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function findWithStepsOrFail(string $id): self
    {
        return static::query()->with('steps')->findOrFail($id);
    }

    public static function createFromPayload(array $payload): self
    {
        return static::query()->create($payload);
    }

    public function updateFromPayload(array $payload): bool
    {
        return $this->update($payload);
    }

    public function replaceSteps(array $steps): void
    {
        $this->steps()->delete();
        $this->steps()->createMany($steps);
    }

    public function toggleActiveStatus(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    public static function getReusableActiveSelectionList(): array
    {
        return static::query()
            ->active()
            ->reusable()
            ->orderedByName()
            ->get(['id', 'name', 'base_price'])
            ->map(fn (self $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'base_price' => (float) $item->base_price,
            ])
            ->all();
    }

    public static function getListingWithStepsCount(): Collection
    {
        return static::query()
            ->withCount('steps')
            ->orderedByName()
            ->get();
    }

    public static function getActiveWithActiveStepsByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return static::query()
            ->active()
            ->whereIn('id', $ids)
            ->with(['steps' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->keyBy('id');
    }

    public static function findByName(string $name): ?self
    {
        return static::query()->where('name', $name)->first();
    }

    protected static function newFactory()
    {
        return ServiceCatalogServiceFactory::new();
    }
}
