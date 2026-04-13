<?php

namespace Ajustatech\ServiceOrder\Database\Models\Procedure;

use Ajustatech\ServiceOrder\Database\Factories\Procedure\ServiceOrderProcedureFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceOrderProcedure extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'service_order_procedures';

    protected $fillable = [
        'name',
        'description',
        'value',
        'help_text',
        'help_image_url',
        'help_video_url',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return ServiceOrderProcedureFactory::new();
    }

    public function media(): HasMany
    {
        return $this->hasMany(ServiceOrderProcedureMedia::class, 'procedure_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function toSelectionOption(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => (string) $this->name,
        ];
    }

    public static function listForSelection(): array
    {
        return static::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (self $procedure) => $procedure->toSelectionOption())
            ->all();
    }

    public static function idMap(): array
    {
        return static::query()
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => true])
            ->all();
    }

    public static function listWithMedia(): Collection
    {
        return static::query()
            ->with('media')
            ->latest()
            ->get();
    }

    public static function findWithMediaOrFail(string $id): self
    {
        return static::query()
            ->with('media')
            ->findOrFail($id);
    }

    public static function findOrFailById(string $id): self
    {
        return static::query()->findOrFail($id);
    }

    public static function createWithMedia(array $data, array $media = []): self
    {
        return DB::transaction(function () use ($data, $media) {
            $procedure = static::query()->create($data);
            ServiceOrderProcedureMedia::createManyForProcedure($procedure->id, $media);

            return $procedure->load('media');
        });
    }

    public function updateWithMedia(array $data, array $media = [], array $deleteMediaIds = []): self
    {
        return DB::transaction(function () use ($data, $media, $deleteMediaIds) {
            $this->update($data);

            $deleteItems = ServiceOrderProcedureMedia::findForProcedureByIds($this->id, $deleteMediaIds);

            foreach ($deleteItems as $mediaItem) {
                if ($mediaItem->disk && $mediaItem->path) {
                    Storage::disk($mediaItem->disk)->delete($mediaItem->path);
                }
            }

            ServiceOrderProcedureMedia::deleteForProcedureByIds($this->id, $deleteMediaIds);
            ServiceOrderProcedureMedia::createManyForProcedure($this->id, $media);

            return $this->load('media');
        });
    }

    public function deleteWithMedia(): void
    {
        DB::transaction(function () {
            $mediaItems = $this->relationLoaded('media')
                ? $this->media
                : $this->media()->get();

            foreach ($mediaItems as $mediaItem) {
                if ($mediaItem->disk && $mediaItem->path) {
                    Storage::disk($mediaItem->disk)->delete($mediaItem->path);
                }
            }

            $this->delete();
        });
    }
}
