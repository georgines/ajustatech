<?php

namespace Ajustatech\ServiceOrder\Database\Models\EquipmentType;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentType\ServiceOrderEquipmentTypeBrandFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceOrderEquipmentTypeBrand extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_equipment_type_brands';

    protected $fillable = [
        'equipment_type_id',
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
        return ServiceOrderEquipmentTypeBrandFactory::new();
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderEquipmentType::class, 'equipment_type_id');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = trim($search);

        if ($term === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('name', 'like', "%{$term}%");
    }

    public static function listForEquipmentType(string $equipmentTypeId, string $search = ''): Collection
    {
        return static::query()
            ->where('equipment_type_id', $equipmentTypeId)
            ->search($search)
            ->orderByDesc('usage_count')
            ->orderBy('name')
            ->get(['id', 'equipment_type_id', 'name', 'usage_count', 'last_used_at']);
    }

    public static function recordUsage(string $equipmentTypeId, string $name): self
    {
        $normalizedName = trim(Str::squish($name));

        return DB::transaction(function () use ($equipmentTypeId, $normalizedName) {
            $brand = static::query()->firstOrNew([
                'equipment_type_id' => $equipmentTypeId,
                'name' => $normalizedName,
            ]);

            $brand->usage_count = ((int) ($brand->usage_count ?? 0)) + 1;
            $brand->last_used_at = Carbon::now();
            $brand->save();

            return $brand->refresh();
        });
    }
}
