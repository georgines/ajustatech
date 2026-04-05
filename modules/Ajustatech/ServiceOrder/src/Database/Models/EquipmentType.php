<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EquipmentType extends Model
{
    use HasFactory;
    use HasUuids;

    private const ACTIVE_SELECTION_CACHE_KEY = 'service_order:equipment_types:active_selection';
    private const ACTIVE_SELECTION_CACHE_TTL_SECONDS = 300;

    protected $table = 'equipment_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'image_disk',
        'image_path',
        'image_original_name',
        'image_mime_type',
        'image_size',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'image_size' => 'integer',
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(EquipmentTypeField::class, 'equipment_type_id');
    }

    public function activeFields(): HasMany
    {
        return $this->fields()->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public static function countAll(): int
    {
        return static::query()->count();
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function findWithFieldsAndOptionsOrFail(string $id): self
    {
        return static::query()->with('fields.options')->findOrFail($id);
    }

    public static function findWithActiveFieldsAndOptionsOrFail(string $id): self
    {
        return static::query()
            ->with([
                'fields' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with([
                        'options' => fn ($optionQuery) => $optionQuery
                            ->where('is_active', true)
                            ->orderBy('sort_order'),
                    ]),
            ])
            ->findOrFail($id);
    }

    public static function createFromPayload(array $payload): self
    {
        $created = static::query()->create($payload);
        static::forgetSelectionCaches();

        return $created;
    }

    public function updateFromPayload(array $payload): bool
    {
        $updated = $this->update($payload);
        static::forgetSelectionCaches();

        return $updated;
    }

    public function getFieldIds(): array
    {
        return $this->fields()->pluck('id')->all();
    }

    public function getFieldsWithOptionsKeyedById(): Collection
    {
        return $this->fields()->with('options')->get()->keyBy('id');
    }

    public function deleteFieldsNotIn(array $keptFieldIds): void
    {
        $this->fields()->whereNotIn('id', $keptFieldIds)->delete();
    }

    public function deleteAllFields(): void
    {
        $this->fields()->delete();
    }

    public static function getActiveSelectionList(): array
    {
        return Cache::remember(
            static::ACTIVE_SELECTION_CACHE_KEY,
            static::ACTIVE_SELECTION_CACHE_TTL_SECONDS,
            fn () => static::query()
                ->active()
                ->orderedByName()
                ->get(['id', 'name'])
                ->map(fn (self $item) => ['id' => $item->id, 'name' => $item->name])
                ->all()
        );
    }

    public static function getListingWithFieldsCount(): Collection
    {
        return static::query()
            ->withCount('fields')
            ->orderedByName()
            ->get();
    }

    public function toggleActiveStatus(): void
    {
        $this->update(['is_active' => !$this->is_active]);
        static::forgetSelectionCaches();
    }

    public function hasImage(): bool
    {
        return filled($this->image_disk) && filled($this->image_path);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->hasImage()) {
            return null;
        }

        try {
            return route('service-order-equipment-types-image', [
                'id' => $this->id,
                'v' => $this->updated_at?->timestamp,
            ], false);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function forgetSelectionCaches(): void
    {
        Cache::forget(static::ACTIVE_SELECTION_CACHE_KEY);
    }

    protected static function newFactory()
    {
        return EquipmentTypeFactory::new();
    }
}
