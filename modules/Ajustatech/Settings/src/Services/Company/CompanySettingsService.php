<?php

namespace Ajustatech\Settings\Services\Company;

use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Ajustatech\Settings\Services\Company\Contracts\CompanySettingsServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanySettingsService implements CompanySettingsServiceInterface
{
    use HandlesFileUploads;

    public function getSettings(): CompanySetting
    {
        return CompanySetting::singleton();
    }

    public function saveSettings(array $payload, ?UploadedFile $logo = null): CompanySetting
    {
        $validated = $this->validatePayload($payload);

        if ($logo) {
            $this->validateLogo($logo);
        }

        $setting = $this->getSettings();
        $oldLogo = [
            'logo_disk' => $setting->logo_disk,
            'logo_path' => $setting->logo_path,
        ];

        $uploadedLogoMetadata = null;
        $shouldDeleteOldLogo = false;

        try {
            $updated = DB::transaction(function () use (
                $validated,
                $setting,
                $logo,
                $oldLogo,
                &$uploadedLogoMetadata,
                &$shouldDeleteOldLogo
            ) {
                $attributes = $validated;

                if ($logo) {
                    $uploadedLogoMetadata = $this->storeLogo($logo, $setting->id);
                    $attributes = array_merge($attributes, $uploadedLogoMetadata);
                    $shouldDeleteOldLogo = filled(Arr::get($oldLogo, 'logo_path'));
                }

                return CompanySetting::updateSingleton($attributes);
            });
        } catch (\Throwable $exception) {
            $this->deleteLogo(
                Arr::get($uploadedLogoMetadata, 'logo_disk'),
                Arr::get($uploadedLogoMetadata, 'logo_path')
            );

            throw $exception;
        }

        if ($shouldDeleteOldLogo) {
            $this->deleteLogo(
                Arr::get($oldLogo, 'logo_disk'),
                Arr::get($oldLogo, 'logo_path')
            );
        }

        return $updated;
    }

    public function validateLogo(UploadedFile $logo): void
    {
        $this->validateUploadedFile(
            file: $logo,
            field: 'logo',
            maxSizeKb: $this->maxSizeKb(),
            allowedExtensions: $this->allowedExtensions(),
            allowedMimeTypes: $this->allowedMimeTypes(),
            mustBeImage: true
        );

        Validator::make(
            ['logo' => $logo],
            ['logo' => ['required', 'dimensions:width=1080,height=1080']],
            ['logo.dimensions' => 'A logo deve ter exatamente 1080x1080 pixels.']
        )->validate();
    }

    public function acceptAttribute(): string
    {
        return collect($this->allowedExtensions())
            ->map(fn (string $extension) => '.'.$extension)
            ->implode(',');
    }

    private function validatePayload(array $payload): array
    {
        $validated = Validator::make($payload, [
            'company_name' => ['required', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:20', new CnpjValidation],
            'address_line' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ])->validate();

        return [
            'company_name' => $this->sanitizeText($validated['company_name'] ?? null, 255),
            'cnpj' => $this->normalizeCnpj($validated['cnpj'] ?? null),
            'address_line' => $this->sanitizeText($validated['address_line'] ?? null, 255),
            'neighborhood' => $this->sanitizeText($validated['neighborhood'] ?? null, 120),
            'city' => $this->sanitizeText($validated['city'] ?? null, 120),
            'state' => $this->normalizeState($validated['state'] ?? null),
            'phone' => $this->sanitizeText($validated['phone'] ?? null, 30),
            'email' => $this->sanitizeEmail($validated['email'] ?? null),
        ];
    }

    private function storeLogo(UploadedFile $logo, string $companySettingId): array
    {
        $this->validateLogo($logo);

        $disk = $this->disk();
        $this->assertUploadDiskExists($disk, 'logo');

        $extension = $this->resolveUploadedFileExtension($logo, $this->allowedExtensions(), 'logo');
        $filename = (string) Str::ulid().'.'.$extension;
        $path = trim($this->directory(), '/').'/'.$companySettingId;

        $storedPath = $this->storeUploadedFile(
            file: $logo,
            disk: $disk,
            directory: $path,
            filename: $filename,
            visibility: $this->visibility()
        );

        $metadata = [
            'logo_disk' => $disk,
            'logo_path' => $storedPath,
            'logo_original_name' => $this->sanitizeUploadedOriginalName($logo, $extension, 180, 'logo'),
            'logo_mime_type' => $logo->getMimeType(),
            'logo_size' => $logo->getSize(),
        ];

        $this->deleteTemporaryUploadedFile($logo);

        return $metadata;
    }

    private function deleteLogo(?string $disk, ?string $path): void
    {
        if (! filled($disk) || ! filled($path)) {
            return;
        }

        if (! array_key_exists((string) $disk, (array) config('filesystems.disks', []))) {
            return;
        }

        $this->deleteUploadedFile((string) $disk, (string) $path);
    }

    private function sanitizeText(?string $value, int $maxLength): ?string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        $normalized = strip_tags($normalized);

        return mb_substr($normalized, 0, $maxLength);
    }

    private function sanitizeEmail(?string $value): ?string
    {
        $email = strtolower((string) $this->sanitizeText($value, 255));

        return $email === '' ? null : $email;
    }

    private function normalizeCnpj(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }

    private function normalizeState(?string $value): ?string
    {
        $state = strtoupper((string) $this->sanitizeText($value, 2));

        return $state === '' ? null : $state;
    }

    private function disk(): string
    {
        $disk = trim((string) config('settings.company.logo.disk', 'public'));
        if ($disk !== '') {
            return $disk;
        }

        $defaultDisk = (string) config('filesystems.default', 'public');

        return $defaultDisk === 'local' ? 'public' : $defaultDisk;
    }

    private function directory(): string
    {
        $directory = trim((string) config('settings.company.logo.directory', 'settings/company/logo'));

        return trim($directory, '/');
    }

    private function visibility(): string
    {
        $visibility = strtolower((string) config('settings.company.logo.visibility', 'public'));

        return in_array($visibility, ['public', 'private'], true) ? $visibility : 'public';
    }

    private function maxSizeKb(): int
    {
        return max((int) config('settings.company.logo.max_size_kb', 5120), 1);
    }

    private function allowedExtensions(): array
    {
        return collect((array) config('settings.company.logo.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']))
            ->map(fn ($extension) => strtolower(trim((string) $extension)))
            ->filter()
            ->values()
            ->all();
    }

    private function allowedMimeTypes(): array
    {
        return collect((array) config('settings.company.logo.allowed_mime_types', ['image/jpeg', 'image/png', 'image/webp']))
            ->map(fn ($mimeType) => strtolower(trim((string) $mimeType)))
            ->filter()
            ->values()
            ->all();
    }
}
