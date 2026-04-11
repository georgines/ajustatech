<?php

namespace Ajustatech\ServiceOrder\Database\Models\EquipmentType;

use Ajustatech\ServiceOrder\Database\Factories\EquipmentType\ServiceOrderEquipmentTypeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceOrderEquipmentType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_equipment_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory()
    {
        return ServiceOrderEquipmentTypeFactory::new();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ServiceOrderEquipmentTypeDocument::class, 'equipment_type_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceOrderEquipmentTypeField::class, 'equipment_type_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public static function listForIndex(string $search = '', string $status = 'all', int $limitPerPage = 10): Collection
    {
        $statusFilter = in_array($status, ['all', 'active', 'inactive'], true) ? $status : 'all';

        return static::query()
            ->select([
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
            ])
            ->withCount([
                'documents',
                'fields',
            ])
            ->search($search)
            ->applyStatusFilter($statusFilter)
            ->latest()
            ->limit($limitPerPage)
            ->get();
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $term = trim($search);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term) {
            $builder->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeApplyStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            default => $query,
        };
    }

    public static function findWithDetailsOrFail(string $id): self
    {
        return static::query()
            ->with(['documents', 'fields'])
            ->findOrFail($id);
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function createWithDetails(array $data, array $documents, array $fields): self
    {
        return DB::transaction(function () use ($data, $documents, $fields) {
            $equipmentType = static::query()->create($data);

            ServiceOrderEquipmentTypeDocument::createManyForEquipmentType($equipmentType->id, $documents);
            ServiceOrderEquipmentTypeField::createManyForEquipmentType($equipmentType->id, $fields);

            return $equipmentType->load(['documents', 'fields']);
        });
    }

    public function updateWithDetails(array $data, array $documents, array $fields, array $filesToDelete): self
    {
        return DB::transaction(function () use ($data, $documents, $fields, $filesToDelete) {
            $this->update($data);

            ServiceOrderEquipmentTypeDocument::query()
                ->where('equipment_type_id', $this->id)
                ->delete();

            ServiceOrderEquipmentTypeField::query()
                ->where('equipment_type_id', $this->id)
                ->delete();

            ServiceOrderEquipmentTypeDocument::createManyForEquipmentType($this->id, $documents);
            ServiceOrderEquipmentTypeField::createManyForEquipmentType($this->id, $fields);

            foreach ($filesToDelete as $fileMeta) {
                $disk = (string) ($fileMeta['disk'] ?? '');
                $path = (string) ($fileMeta['path'] ?? '');

                if ($disk === '' || $path === '') {
                    continue;
                }

                Storage::disk($disk)->delete($path);
            }

            return $this;
        });
    }

    public function deleteWithDetails(): void
    {
        DB::transaction(function () {
            $documents = $this->relationLoaded('documents') ? $this->documents : $this->documents()->get();
            $fields = $this->relationLoaded('fields') ? $this->fields : $this->fields()->get();

            $this->deleteStoredFiles($documents, $fields);
            $this->delete();
        });
    }

    public function duplicateWithDetails(array $data, array $documents, array $fields): self
    {
        return DB::transaction(function () use ($data, $documents, $fields) {
            $clone = static::query()->create($data);

            ServiceOrderEquipmentTypeDocument::createManyForEquipmentType($clone->id, $documents);
            ServiceOrderEquipmentTypeField::createManyForEquipmentType($clone->id, $fields);

            return $clone;
        });
    }

    public function toggleStatus(): void
    {
        $this->update([
            'is_active' => ! (bool) $this->is_active,
        ]);
    }

    public static function nextDuplicateName(string $originalName): string
    {
        $base = trim($originalName) === '' ? 'Tipo de equipamento' : trim($originalName);

        $firstCopyName = "{$base} (Copia)";
        $likePattern = "{$base} (Copia %)";

        $existingCopyNames = static::query()
            ->where('name', $firstCopyName)
            ->orWhere('name', 'like', $likePattern)
            ->pluck('name')
            ->all();

        if (empty($existingCopyNames)) {
            return $firstCopyName;
        }

        $maxSuffix = 1;

        foreach ($existingCopyNames as $name) {
            if ($name === $firstCopyName) {
                $maxSuffix = max($maxSuffix, 1);
                continue;
            }

            $suffix = Str::of($name)
                ->after("{$base} (Copia ")
                ->before(')')
                ->toString();

            if (is_numeric($suffix)) {
                $maxSuffix = max($maxSuffix, (int) $suffix);
            }
        }

        $nextSuffix = $maxSuffix + 1;

        return "{$base} (Copia {$nextSuffix})";
    }

    private function deleteStoredFiles(Collection $documents, Collection $fields): void
    {
        $files = $documents
            ->map(fn (ServiceOrderEquipmentTypeDocument $document) => [
                'disk' => $document->disk,
                'path' => $document->path,
            ])
            ->merge($fields->map(fn (ServiceOrderEquipmentTypeField $field) => [
                'disk' => $field->disk,
                'path' => $field->path,
            ]));

        foreach ($files as $fileMeta) {
            $disk = (string) ($fileMeta['disk'] ?? '');
            $path = (string) ($fileMeta['path'] ?? '');

            if ($disk === '' || $path === '') {
                continue;
            }

            Storage::disk($disk)->delete($path);
        }
    }
}
