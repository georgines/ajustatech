<?php

namespace Ajustatech\Core\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\Response;

trait HandlesFileUploads
{
    protected function assertUploadDiskExists(string $disk, string $field = 'file'): void
    {
        $disks = (array) config('filesystems.disks', []);

        if (!array_key_exists($disk, $disks)) {
            throw ValidationException::withMessages([
                $field => 'Disco de upload configurado nao existe.',
            ]);
        }
    }

    protected function validateUploadedFile(
        UploadedFile $file,
        string $field,
        int $maxSizeKb,
        array $allowedExtensions = [],
        array $allowedMimeTypes = [],
        bool $mustBeImage = false
    ): void {
        $rules = [
            $field => ['required', 'file', 'max:' . max($maxSizeKb, 1)],
        ];

        if ($mustBeImage) {
            $rules[$field][] = 'image';
        }

        if (!empty($allowedExtensions)) {
            $rules[$field][] = 'extensions:' . implode(',', $allowedExtensions);
        }

        if (!empty($allowedMimeTypes)) {
            $rules[$field][] = 'mimetypes:' . implode(',', $allowedMimeTypes);
        }

        Validator::make([$field => $file], $rules)->validate();
    }

    protected function resolveUploadedFileExtension(
        UploadedFile $file,
        array $allowedExtensions = [],
        string $field = 'file'
    ): string {
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension()));

        if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                $field => 'Extensao de arquivo nao permitida.',
            ]);
        }

        return $extension;
    }

    protected function sanitizeUploadedOriginalName(
        UploadedFile $file,
        string $extension,
        int $maxLength = 180,
        string $fallbackName = 'file'
    ): string {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safe = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', (string) $name) ?: $fallbackName;
        $safe = trim($safe);

        if ($safe === '') {
            $safe = $fallbackName;
        }

        return Str::limit($safe, $maxLength, '') . '.' . $extension;
    }

    protected function storeUploadedFile(
        UploadedFile $file,
        string $disk,
        string $directory,
        ?string $filename = null,
        ?string $visibility = null
    ): string {
        $options = ['disk' => $disk];

        if ($visibility !== null) {
            $options['visibility'] = $visibility;
        }

        return (string) $file->storeAs(trim($directory, '/'), $filename ?? $file->hashName(), $options);
    }

    protected function deleteUploadedFile(string $disk, string $path): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    protected function deleteTemporaryUploadedFile(UploadedFile $file): void
    {
        if ($file instanceof TemporaryUploadedFile) {
            $file->delete();
        }
    }

    protected function temporaryUploadedFileUrl(?UploadedFile $file): ?string
    {
        if (!$file || !method_exists($file, 'temporaryUrl')) {
            return null;
        }

        return (string) $file->temporaryUrl();
    }

    protected function streamUploadedFile(
        string $disk,
        string $path,
        ?string $mimeType = null,
        string $cacheControl = 'public, max-age=31536000, immutable'
    ): Response {
        $this->assertUploadDiskExists($disk);

        $storage = Storage::disk($disk);

        if (!$storage->exists($path)) {
            abort(404);
        }

        $stream = $storage->readStream($path);
        if ($stream === false) {
            abort(404);
        }

        $resolvedMimeType = $mimeType ?: ($storage->mimeType($path) ?: 'application/octet-stream');
        $lastModified = (int) $storage->lastModified($path);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => (string) $resolvedMimeType,
            'Cache-Control' => $cacheControl,
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        ]);
    }
}
