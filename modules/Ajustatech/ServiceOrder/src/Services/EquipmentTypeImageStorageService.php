<?php

namespace Ajustatech\ServiceOrder\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EquipmentTypeImageStorageService
{
    public function validate(UploadedFile $image): void
    {
        $rules = [
            'image' => [
                'required',
                'file',
                'image',
                'max:' . $this->maxSizeKb(),
                'extensions:' . implode(',', $this->allowedExtensions()),
            ],
        ];

        $allowedMimeTypes = $this->allowedMimeTypes();
        if (!empty($allowedMimeTypes)) {
            $rules['image'][] = 'mimetypes:' . implode(',', $allowedMimeTypes);
        }

        Validator::make(['image' => $image], $rules)->validate();
    }

    public function store(UploadedFile $image, string $equipmentTypeId): array
    {
        $this->validate($image);

        $disk = $this->disk();
        $this->ensureDiskExists($disk);

        $extension = $this->resolveExtension($image);
        $filename = (string) Str::ulid() . '.' . $extension;
        $directory = trim($this->directory(), '/');
        $path = trim($directory . '/' . $equipmentTypeId, '/');

        $storedPath = Storage::disk($disk)->putFileAs(
            $path,
            $image,
            $filename,
            ['visibility' => $this->visibility()]
        );

        if (!$storedPath) {
            throw ValidationException::withMessages([
                'image' => 'Nao foi possivel armazenar a imagem do tipo de equipamento.',
            ]);
        }

        return [
            'image_disk' => $disk,
            'image_path' => $storedPath,
            'image_original_name' => $this->sanitizeOriginalName($image),
            'image_mime_type' => $image->getMimeType(),
            'image_size' => $image->getSize(),
        ];
    }

    public function delete(?string $disk, ?string $path): void
    {
        if (!filled($disk) || !filled($path)) {
            return;
        }

        if (!array_key_exists((string) $disk, (array) config('filesystems.disks', []))) {
            return;
        }

        try {
            Storage::disk((string) $disk)->delete((string) $path);
        } catch (\Throwable) {
        }
    }

    public function acceptAttribute(): string
    {
        return collect($this->allowedExtensions())
            ->map(fn (string $extension) => '.' . $extension)
            ->implode(',');
    }

    private function resolveExtension(UploadedFile $image): string
    {
        $extension = strtolower((string) ($image->guessExtension() ?: $image->getClientOriginalExtension()));
        $allowedExtensions = $this->allowedExtensions();

        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'image' => 'Extensao de imagem nao permitida.',
            ]);
        }

        return $extension;
    }

    private function ensureDiskExists(string $disk): void
    {
        $disks = (array) config('filesystems.disks', []);
        if (array_key_exists($disk, $disks)) {
            return;
        }

        throw ValidationException::withMessages([
            'image' => 'Disco configurado para imagem de tipo de equipamento nao existe.',
        ]);
    }

    private function disk(): string
    {
        return (string) config('service_order.equipment_type_images.disk', config('filesystems.default'));
    }

    private function directory(): string
    {
        return (string) config('service_order.equipment_type_images.directory', 'equipment-types');
    }

    private function visibility(): string
    {
        $value = strtolower((string) config('service_order.equipment_type_images.visibility', 'private'));

        return in_array($value, ['public', 'private'], true) ? $value : 'private';
    }

    private function maxSizeKb(): int
    {
        return max((int) config('service_order.equipment_type_images.max_size_kb', 5120), 1);
    }

    private function allowedExtensions(): array
    {
        $extensions = Arr::wrap(config('service_order.equipment_type_images.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']));

        return collect($extensions)
            ->map(fn ($extension) => strtolower(trim((string) $extension)))
            ->filter()
            ->values()
            ->all();
    }

    private function allowedMimeTypes(): array
    {
        $types = Arr::wrap(config('service_order.equipment_type_images.allowed_mime_types', ['image/jpeg', 'image/png', 'image/webp']));

        return collect($types)
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter()
            ->values()
            ->all();
    }

    private function sanitizeOriginalName(UploadedFile $image): string
    {
        $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', (string) $name) ?: 'image';
        $safe = trim($safe);

        if ($safe === '') {
            $safe = 'image';
        }

        $safe = Str::limit($safe, 180, '');
        $extension = $this->resolveExtension($image);

        return $safe . '.' . $extension;
    }
}
