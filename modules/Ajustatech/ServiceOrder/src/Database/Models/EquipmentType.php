<?php

namespace Ajustatech\ServiceOrder\Database\Models;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class EquipmentType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'equipment_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
        return static::query()->create($payload);
    }

    public function updateFromPayload(array $payload): bool
    {
        return $this->update($payload);
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
        return static::query()
            ->active()
            ->orderedByName()
            ->get(['id', 'name'])
            ->map(fn (self $item) => ['id' => $item->id, 'name' => $item->name])
            ->all();
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
    }

    protected static function newFactory()
    {
        return EquipmentTypeFactory::new();
    }
}
