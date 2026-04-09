<?php

namespace Ajustatech\ServiceOrder\Services\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedure;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Ajustatech\ServiceOrder\Livewire\Procedure\ProcedureManagement;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureMediaServiceInterface;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProcedureMediaService implements ProcedureMediaServiceInterface
{
    public function initializeCreateState(ProcedureManagement $component): void
    {
        $component->videoItems = [];
        $component->imageItems = [];
        $component->pdfItems = [];
        $this->resetAddMediaForm($component);
    }

    public function fillFromProcedure(ProcedureManagement $component, ServiceOrderProcedure $procedure): void
    {
        $component->existingMedia = $procedure->media
            ->map(fn ($media) => [
                'id' => $media->id,
                'type' => $media->type,
                'display_name' => $media->display_name,
                'url' => $media->url,
                'disk' => $media->disk,
                'path' => $media->path,
                'description' => $media->description,
                'original_name' => $media->original_name,
                'public_url' => $media->path ? route('service-order-procedures-media-file', ['id' => $media->id]) : null,
            ])
            ->toArray();

        $component->hasHelp = $this->hasHelpContent($component);
    }

    public function updatedHasHelp(ProcedureManagement $component, bool $value): void
    {
        if ($value) {
            return;
        }

        $component->helpText = '';
        $component->videoItems = [];
        $component->imageItems = [];
        $component->pdfItems = [];
        $this->resetAddMediaForm($component);
        $component->deleteMediaIds = array_merge($component->deleteMediaIds, array_column($component->existingMedia, 'id'));
        $component->existingMedia = [];
        $component->resetValidation();
    }

    public function addMediaItem(ProcedureManagement $component): void
    {
        if (! $component->hasHelp) {
            $component->addError('hasHelp', trans('service-order::messages.procedure_help_enable_first'));

            return;
        }

        $component->newMediaName = $this->sanitizeText($component->newMediaName, 255);
        $component->newMediaDescription = $this->sanitizeText($component->newMediaDescription, 500);
        $component->newMediaUrl = $this->sanitizeUrl($component->newMediaUrl);

        $rules = [
            'newMediaType' => 'required|in:image,video,pdf',
            'newMediaName' => 'required|string|max:255',
            'newMediaDescription' => 'nullable|string|max:500',
        ];

        if ($component->newMediaType === 'video') {
            $rules['newMediaUrl'] = 'required|url|max:1000';
        }

        if ($component->newMediaType === 'image') {
            $rules['newMediaFile'] = 'required|image|max:5120';
        }

        if ($component->newMediaType === 'pdf') {
            $rules['newMediaFile'] = 'required|file|mimes:pdf|max:10240';
        }

        $component->validate($rules, [], $this->validationAttributes());

        if ($component->newMediaType === 'video') {
            $component->videoItems[] = $this->newVideoMediaItem($component);
            $this->resetAddMediaForm($component);
            $component->dispatch('procedure-media-added');

            return;
        }

        if ($component->newMediaType === 'image') {
            $component->imageItems[] = $this->newFileMediaItem($component);
            $this->resetAddMediaForm($component);
            $component->dispatch('procedure-media-added');

            return;
        }

        $component->pdfItems[] = $this->newFileMediaItem($component);
        $this->resetAddMediaForm($component);
        $component->dispatch('procedure-media-added');
    }

    public function resetAddMediaForm(ProcedureManagement $component): void
    {
        $component->newMediaType = 'image';
        $component->newMediaName = '';
        $component->newMediaDescription = '';
        $component->newMediaUrl = '';
        $component->newMediaFile = null;
        $component->resetValidation(['newMediaType', 'newMediaName', 'newMediaDescription', 'newMediaUrl', 'newMediaFile']);
    }

    public function addVideoItem(ProcedureManagement $component): void
    {
        $component->videoItems[] = ['url' => '', 'name' => '', 'description' => ''];
    }

    public function removeVideoItem(ProcedureManagement $component, int $index): void
    {
        if (! isset($component->videoItems[$index])) {
            return;
        }

        unset($component->videoItems[$index]);
        $component->videoItems = array_values($component->videoItems);
    }

    public function addImageItem(ProcedureManagement $component): void
    {
        $component->imageItems[] = ['file' => null, 'name' => '', 'description' => ''];
    }

    public function removeImageItem(ProcedureManagement $component, int $index): void
    {
        if (! isset($component->imageItems[$index])) {
            return;
        }

        unset($component->imageItems[$index]);
        $component->imageItems = array_values($component->imageItems);
    }

    public function addPdfItem(ProcedureManagement $component): void
    {
        $component->pdfItems[] = ['file' => null, 'name' => '', 'description' => ''];
    }

    public function removePdfItem(ProcedureManagement $component, int $index): void
    {
        if (! isset($component->pdfItems[$index])) {
            return;
        }

        unset($component->pdfItems[$index]);
        $component->pdfItems = array_values($component->pdfItems);
    }

    public function removeExistingMedia(ProcedureManagement $component, string $mediaId): void
    {
        $component->deleteMediaIds[] = $mediaId;
        $component->deleteMediaIds = array_values(array_unique($component->deleteMediaIds));
        $component->existingMedia = array_values(array_filter(
            $component->existingMedia,
            fn (array $media) => $media['id'] !== $mediaId
        ));
    }

    public function buildMediaPayload(ProcedureManagement $component): array
    {
        if (! $component->hasHelp) {
            return [];
        }

        $media = [];
        $sortOrder = 0;

        foreach ($component->videoItems as $video) {
            $url = $this->nullableValue((string) ($video['url'] ?? ''));
            $name = $this->nullableValue((string) ($video['name'] ?? ''));
            $description = $this->nullableValue((string) ($video['description'] ?? ''));

            if (! $url) {
                continue;
            }

            $media[] = [
                'type' => ServiceOrderProcedureMedia::TYPE_VIDEO,
                'display_name' => $name,
                'url' => $url,
                'description' => $description,
                'sort_order' => $sortOrder++,
            ];
        }

        foreach ($component->imageItems as $item) {
            $file = $item['file'] ?? null;
            $description = $this->nullableValue((string) ($item['description'] ?? ''));

            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = $file->store('service-order/procedures/images', 'public');

            $media[] = [
                'type' => ServiceOrderProcedureMedia::TYPE_IMAGE,
                'display_name' => $this->nullableValue((string) ($item['name'] ?? '')),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'size' => $file->getSize(),
                'description' => $description,
                'sort_order' => $sortOrder++,
            ];
        }

        foreach ($component->pdfItems as $item) {
            $file = $item['file'] ?? null;
            $description = $this->nullableValue((string) ($item['description'] ?? ''));

            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $path = $file->store('service-order/procedures/pdfs', 'public');

            $media[] = [
                'type' => ServiceOrderProcedureMedia::TYPE_PDF,
                'display_name' => $this->nullableValue((string) ($item['name'] ?? '')),
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => strtolower((string) $file->getClientOriginalExtension()),
                'size' => $file->getSize(),
                'description' => $description,
                'sort_order' => $sortOrder++,
            ];
        }

        return $media;
    }

    public function hasHelpContent(ProcedureManagement $component): bool
    {
        if ($this->nullableValue($component->helpText) !== null) {
            return true;
        }

        if (! empty($component->existingMedia)) {
            return true;
        }

        foreach ($component->videoItems as $video) {
            if ($this->nullableValue((string) ($video['url'] ?? '')) !== null) {
                return true;
            }
        }

        foreach ($component->imageItems as $item) {
            if (($item['file'] ?? null) instanceof TemporaryUploadedFile) {
                return true;
            }
        }

        foreach ($component->pdfItems as $item) {
            if (($item['file'] ?? null) instanceof TemporaryUploadedFile) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeText(?string $value, int $limit): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $value));
        $withoutTags = strip_tags((string) $normalized);

        return mb_substr($withoutTags, 0, $limit);
    }

    private function sanitizeUrl(?string $value): string
    {
        return mb_substr(strip_tags(trim((string) $value)), 0, 1000);
    }

    private function nullableValue(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function validationAttributes(): array
    {
        return [
            'newMediaType' => trans('service-order::messages.procedure_help_media_type'),
            'newMediaName' => trans('service-order::messages.procedure_help_media_name'),
            'newMediaDescription' => trans('service-order::messages.procedure_help_media_text'),
            'newMediaUrl' => trans('service-order::messages.procedure_help_video'),
            'newMediaFile' => trans('service-order::messages.procedure_help_media_file'),
        ];
    }

    private function newVideoMediaItem(ProcedureManagement $component): array
    {
        return [
            'url' => $component->newMediaUrl,
            'name' => $component->newMediaName,
            'description' => $component->newMediaDescription,
        ];
    }

    private function newFileMediaItem(ProcedureManagement $component): array
    {
        return [
            'file' => $component->newMediaFile,
            'name' => $component->newMediaName,
            'description' => $component->newMediaDescription,
        ];
    }
}
