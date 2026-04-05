<?php

namespace Ajustatech\ServiceOrder\Services;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EquipmentTypeImageStorageService
{
    use HandlesFileUploads;

    public function validate(UploadedFile $image): void
    {
        $this->validateUploadedFile(
            file: $image,
            field: 'image',
            maxSizeKb: $this->maxSizeKb(),
            allowedExtensions: $this->allowedExtensions(),
            allowedMimeTypes: $this->allowedMimeTypes(),
            mustBeImage: true
        );
    }

    public function store(UploadedFile $image, string $equipmentTypeId): array
    {
        $this->validate($image);

        $disk = $this->disk();
        $this->assertUploadDiskExists($disk, 'image');

        $extension = $this->resolveUploadedFileExtension($image, $this->allowedExtensions(), 'image');
        $filename = (string) Str::ulid() . '.' . $extension;
        $directory = trim($this->directory(), '/');
        $path = trim($directory . '/' . $equipmentTypeId, '/');

        $storedPath = $this->storeUploadedFile(
            file: $image,
            disk: $disk,
            directory: $path,
            filename: $filename,
            visibility: $this->visibility()
        );

        $metadata = [
            'image_disk' => $disk,
            'image_path' => $storedPath,
            'image_original_name' => $this->sanitizeUploadedOriginalName($image, $extension, 180, 'image'),
            'image_mime_type' => $image->getMimeType(),
            'image_size' => $image->getSize(),
        ];

        $this->deleteTemporaryUploadedFile($image);

        return $metadata;
    }

    public function delete(?string $disk, ?string $path): void
    {
        if (!filled($disk) || !filled($path)) {
            return;
        }

        if (!array_key_exists((string) $disk, (array) config('filesystems.disks', []))) {
            return;
        }

        $this->deleteUploadedFile((string) $disk, (string) $path);
    }

    public function acceptAttribute(): string
    {
        return collect($this->allowedExtensions())
            ->map(fn (string $extension) => '.' . $extension)
            ->implode(',');
    }

    private function disk(): string
    {
        $diskFromConfig = trim((string) config('service_order.equipment_type_images.disk', ''));
        if ($diskFromConfig !== '') {
            return $diskFromConfig;
        }

        $disk = trim((string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_DISK', ''));
        if ($disk !== '') {
            return $disk;
        }

        $defaultDisk = (string) config('filesystems.default', 'public');

        return $defaultDisk === 'local' ? 'public' : $defaultDisk;
    }

    private function directory(): string
    {
        $directory = trim((string) config('service_order.equipment_type_images.directory', ''));
        if ($directory !== '') {
            return trim($directory, '/');
        }

        return trim((string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_DIRECTORY', 'equipment-types'), '/');
    }

    private function visibility(): string
    {
        $value = strtolower((string) config('service_order.equipment_type_images.visibility', ''));
        if ($value === '') {
            $value = strtolower((string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_VISIBILITY', 'public'));
        }

        return in_array($value, ['public', 'private'], true) ? $value : 'private';
    }

    private function maxSizeKb(): int
    {
        $maxSizeFromConfig = (int) config('service_order.equipment_type_images.max_size_kb', 0);
        if ($maxSizeFromConfig > 0) {
            return max($maxSizeFromConfig, 1);
        }

        return max((int) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_MAX_SIZE_KB', 5120), 1);
    }

    private function allowedExtensions(): array
    {
        $extensionsFromConfig = Arr::wrap(config('service_order.equipment_type_images.allowed_extensions', []));
        $extensions = !empty($extensionsFromConfig)
            ? $extensionsFromConfig
            : Arr::wrap(explode(',', (string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,webp')));

        return collect($extensions)
            ->map(fn ($extension) => strtolower(trim((string) $extension)))
            ->filter()
            ->values()
            ->all();
    }

    private function allowedMimeTypes(): array
    {
        $typesFromConfig = Arr::wrap(config('service_order.equipment_type_images.allowed_mime_types', []));
        $types = !empty($typesFromConfig)
            ? $typesFromConfig
            : Arr::wrap(explode(',', (string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_ALLOWED_MIME_TYPES', 'image/jpeg,image/png,image/webp')));

        return collect($types)
            ->map(fn ($type) => strtolower(trim((string) $type)))
            ->filter()
            ->values()
            ->all();
    }

}
