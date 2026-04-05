<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcedureService implements ProcedureServiceInterface
{
    public function listProcedures(): Collection
    {
        return ServiceOrderProcedure::query()
            ->with('media')
            ->latest()
            ->get();
    }

    public function findProcedure(string $id): ServiceOrderProcedure
    {
        return ServiceOrderProcedure::query()
            ->with('media')
            ->findOrFail($id);
    }

    public function createProcedure(array $data, array $media = []): ServiceOrderProcedure
    {
        return DB::transaction(function () use ($data, $media) {
            $procedure = ServiceOrderProcedure::query()->create($data);
            $this->createMediaItems($procedure, $media);

            return $procedure->fresh('media');
        });
    }

    public function updateProcedure(string $id, array $data, array $media = [], array $deleteMediaIds = []): ServiceOrderProcedure
    {
        return DB::transaction(function () use ($id, $data, $media, $deleteMediaIds) {
            $procedure = $this->findProcedure($id);
            $procedure->update($data);
            $this->deleteMediaItems($procedure, $deleteMediaIds);
            $this->createMediaItems($procedure, $media);

            return $procedure->fresh('media');
        });
    }

    public function deleteProcedure(string $id): void
    {
        DB::transaction(function () use ($id) {
            $procedure = $this->findProcedure($id);

            foreach ($procedure->media as $media) {
                if ($media->disk && $media->path) {
                    Storage::disk($media->disk)->delete($media->path);
                }
            }

            $procedure->delete();
        });
    }

    private function createMediaItems(ServiceOrderProcedure $procedure, array $media): void
    {
        if (empty($media)) {
            return;
        }

        $rows = collect($media)
            ->map(function (array $item, int $index) use ($procedure) {
                return [
                    'id' => (string) Str::uuid(),
                    'procedure_id' => $procedure->id,
                    'type' => $item['type'],
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

        ServiceOrderProcedureMedia::query()->insert($rows);
    }

    private function deleteMediaItems(ServiceOrderProcedure $procedure, array $deleteMediaIds): void
    {
        if (empty($deleteMediaIds)) {
            return;
        }

        $ids = array_values(array_filter($deleteMediaIds));

        if (empty($ids)) {
            return;
        }

        $items = $procedure->media()
            ->whereIn('id', $ids)
            ->get();

        foreach ($items as $media) {
            if ($media->disk && $media->path) {
                Storage::disk($media->disk)->delete($media->path);
            }
        }

        $procedure->media()
            ->whereIn('id', $ids)
            ->delete();
    }
}
