<?php

namespace Ajustatech\ServiceOrder\Database\Models\Procedure;

use Ajustatech\ServiceOrder\Database\Factories\Procedure\ServiceOrderProcedureMediaFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceOrderProcedureMedia extends Model
{
    use HasFactory;
    use HasUuids;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_PDF = 'pdf';

    protected $table = 'service_order_procedure_media';

    protected $fillable = [
        'procedure_id',
        'type',
        'display_name',
        'url',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function newFactory()
    {
        return ServiceOrderProcedureMediaFactory::new();
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(ServiceOrderProcedure::class, 'procedure_id');
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function createManyForProcedure(string $procedureId, array $media): void
    {
        if (empty($media)) {
            return;
        }

        $rows = collect($media)
            ->map(function (array $item, int $index) use ($procedureId) {
                return [
                    'id' => (string) Str::uuid(),
                    'procedure_id' => $procedureId,
                    'type' => $item['type'],
                    'display_name' => $item['display_name'] ?? null,
                    'url' => $item['url'] ?? null,
                    'disk' => $item['disk'] ?? null,
                    'path' => $item['path'] ?? null,
                    'original_name' => $item['original_name'] ?? null,
                    'mime_type' => $item['mime_type'] ?? null,
                    'extension' => $item['extension'] ?? null,
                    'size' => $item['size'] ?? null,
                    'description' => $item['description'] ?? null,
                    'sort_order' => $item['sort_order'] ?? $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->all();

        static::query()->insert($rows);
    }

    public static function findForProcedureByIds(string $procedureId, array $ids): Collection
    {
        $filteredIds = array_values(array_filter($ids));

        if (empty($filteredIds)) {
            return new Collection();
        }

        return static::query()
            ->where('procedure_id', $procedureId)
            ->whereIn('id', $filteredIds)
            ->get();
    }

    public static function deleteForProcedureByIds(string $procedureId, array $ids): void
    {
        $filteredIds = array_values(array_filter($ids));

        if (empty($filteredIds)) {
            return;
        }

        static::query()
            ->where('procedure_id', $procedureId)
            ->whereIn('id', $filteredIds)
            ->delete();
    }
}
