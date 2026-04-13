<?php

namespace Ajustatech\ServiceOrder\Database\Models\EquipmentType;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentType\ServiceOrderEquipmentTypeModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeModel extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_equipment_type_models';

    protected $fillable = [
        'equipment_type_brand_id',
        'name',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return ServiceOrderEquipmentTypeModelFactory::new();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentTypeBrand::class, 'equipment_type_brand_id');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = trim($search);

        if ($term === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('name', 'like', "%{$term}%");
    }

    public function toServiceOrderOption(): array
    {
        return [
            'id' => (string) $this->id,
            'equipment_type_brand_id' => (string) $this->equipment_type_brand_id,
            'name' => (string) $this->name,
            'usage_count' => (int) $this->usage_count,
            'last_used_at' => optional($this->last_used_at)?->toDateTimeString(),
        ];
    }

    public static function listForBrand(string $brandId, string $search = ''): Collection
    {
        $term = trim($search);

        if ($term === '') {
            return collect();
        }

        return static::query()
            ->where('equipment_type_brand_id', $brandId)
            ->search($term)
            ->orderByDesc('usage_count')
            ->orderBy('name')
            ->get(['id', 'equipment_type_brand_id', 'name', 'usage_count', 'last_used_at']);
    }

    public static function findForBrand(string $brandId, string $modelId): ?self
    {
        return static::query()
            ->where('equipment_type_brand_id', $brandId)
            ->find($modelId);
    }

    public static function recordUsage(string $brandId, string $name): self
    {
        $normalizedName = trim(Str::squish($name));

        return DB::transaction(function () use ($brandId, $normalizedName) {
            $model = static::query()->firstOrNew([
                'equipment_type_brand_id' => $brandId,
                'name' => $normalizedName,
            ]);

            $model->usage_count = ((int) ($model->usage_count ?? 0)) + 1;
            $model->last_used_at = Carbon::now();
            $model->save();

            return $model->refresh();
        });
    }
}
