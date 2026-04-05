<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class ProcedureManagement extends Component
{
    use WithFileUploads;

    public $title;
    public $mode = 'create';
    public $procedureId = null;

    public $name = '';
    public $description = '';
    public $value = null;
    public bool $hasHelp = false;
    public $helpText = '';

    public array $videoItems = [];
    public array $imageItems = [];
    public array $pdfItems = [];
    public array $existingMedia = [];
    public array $deleteMediaIds = [];

    public string $newMediaType = 'image';
    public string $newMediaName = '';
    public string $newMediaDescription = '';
    public string $newMediaUrl = '';
    public mixed $newMediaFile = null;

    public function mount(ProcedureServiceInterface $service, ?string $id = null): void
    {
        $this->title = trans('service-order::messages.procedure_create_title');
        $this->videoItems = [];
        $this->imageItems = [];
        $this->pdfItems = [];

        if (!$id) {
            return;
        }

        $procedure = $service->findProcedure($id);
        $this->mode = 'edit';
        $this->procedureId = $procedure->id;
        $this->name = $procedure->name;
        $this->description = (string) $procedure->description;
        $this->value = (float) $procedure->value;
        $this->helpText = (string) $procedure->help_text;
        $this->existingMedia = $procedure->media
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
        $this->hasHelp = $this->hasHelpContent();
        $this->title = trans('service-order::messages.procedure_edit_title');
    }

    public function save(ProcedureServiceInterface $service)
    {
        $this->sanitizeInputs();

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'value' => 'required|numeric|min:0|max:999999.99',
            'hasHelp' => 'required|boolean',
            'helpText' => 'nullable|string|max:2000',
        ];

        if ($this->hasHelp) {
            $rules['videoItems.*.url'] = 'nullable|url|max:1000';
            $rules['videoItems.*.name'] = 'required_with:videoItems.*.url|string|max:255';
            $rules['videoItems.*.description'] = 'nullable|string|max:500';
            $rules['imageItems.*.file'] = 'nullable|image|max:5120';
            $rules['imageItems.*.name'] = 'required_with:imageItems.*.file|string|max:255';
            $rules['imageItems.*.description'] = 'nullable|string|max:500';
            $rules['pdfItems.*.file'] = 'nullable|file|mimes:pdf|max:10240';
            $rules['pdfItems.*.name'] = 'required_with:pdfItems.*.file|string|max:255';
            $rules['pdfItems.*.description'] = 'nullable|string|max:500';
        }

        $this->validate($rules, [], $this->validationAttributes());

        if ($this->hasHelp && !$this->hasHelpContent()) {
            $this->addError('hasHelp', trans('service-order::messages.procedure_help_required'));
            return;
        }

        $media = $this->buildMediaPayload();

        $payload = [
            'name' => $this->name,
            'description' => $this->nullableValue($this->description),
            'value' => $this->value,
            'help_text' => $this->hasHelp ? $this->nullableValue($this->helpText) : null,
            'help_image_url' => null,
            'help_video_url' => null,
        ];

        if ($this->mode === 'edit' && $this->procedureId) {
            $service->updateProcedure($this->procedureId, $payload, $media, $this->deleteMediaIds);
        } else {
            $service->createProcedure($payload, $media);
        }

        return redirect()->route('service-order-procedures-show');
    }

    public function updatedHasHelp(bool $value): void
    {
        if ($value) {
            return;
        }

        $this->helpText = '';
        $this->videoItems = [];
        $this->imageItems = [];
        $this->pdfItems = [];
        $this->resetNewMediaForm();
        $this->deleteMediaIds = array_merge($this->deleteMediaIds, array_column($this->existingMedia, 'id'));
        $this->existingMedia = [];
        $this->resetValidation();
    }

    public function addMediaItem(): void
    {
        if (!$this->hasHelp) {
            $this->addError('hasHelp', trans('service-order::messages.procedure_help_enable_first'));
            return;
        }

        $this->newMediaName = $this->sanitizeText($this->newMediaName, 255);
        $this->newMediaDescription = $this->sanitizeText($this->newMediaDescription, 500);
        $this->newMediaUrl = $this->sanitizeUrl($this->newMediaUrl);

        $rules = [
            'newMediaType' => 'required|in:image,video,pdf',
            'newMediaName' => 'required|string|max:255',
            'newMediaDescription' => 'nullable|string|max:500',
        ];

        if ($this->newMediaType === 'video') {
            $rules['newMediaUrl'] = 'required|url|max:1000';
        }

        if ($this->newMediaType === 'image') {
            $rules['newMediaFile'] = 'required|image|max:5120';
        }

        if ($this->newMediaType === 'pdf') {
            $rules['newMediaFile'] = 'required|file|mimes:pdf|max:10240';
        }

        $this->validate($rules, [], $this->validationAttributes());

        if ($this->newMediaType === 'video') {
            $this->videoItems[] = $this->newVideoMediaItem();
            $this->resetNewMediaForm();
            $this->dispatch('procedure-media-added');

            return;
        }

        if ($this->newMediaType === 'image') {
            $this->imageItems[] = $this->newFileMediaItem();
            $this->resetNewMediaForm();
            $this->dispatch('procedure-media-added');

            return;
        }

        $this->pdfItems[] = $this->newFileMediaItem();
        $this->resetNewMediaForm();
        $this->dispatch('procedure-media-added');
    }

    public function resetAddMediaForm(): void
    {
        $this->resetNewMediaForm();
    }

    public function addVideoItem(): void
    {
        $this->videoItems[] = ['url' => '', 'name' => '', 'description' => ''];
    }

    public function removeVideoItem(int $index): void
    {
        if (!isset($this->videoItems[$index])) {
            return;
        }

        unset($this->videoItems[$index]);
        $this->videoItems = array_values($this->videoItems);
    }

    public function addImageItem(): void
    {
        $this->imageItems[] = ['file' => null, 'name' => '', 'description' => ''];
    }

    public function removeImageItem(int $index): void
    {
        if (!isset($this->imageItems[$index])) {
            return;
        }

        unset($this->imageItems[$index]);
        $this->imageItems = array_values($this->imageItems);
    }

    public function addPdfItem(): void
    {
        $this->pdfItems[] = ['file' => null, 'name' => '', 'description' => ''];
    }

    public function removePdfItem(int $index): void
    {
        if (!isset($this->pdfItems[$index])) {
            return;
        }

        unset($this->pdfItems[$index]);
        $this->pdfItems = array_values($this->pdfItems);
    }

    public function removeExistingMedia(string $mediaId): void
    {
        $this->deleteMediaIds[] = $mediaId;
        $this->deleteMediaIds = array_values(array_unique($this->deleteMediaIds));
        $this->existingMedia = array_values(array_filter(
            $this->existingMedia,
            fn (array $media) => $media['id'] !== $mediaId
        ));
    }

    private function buildMediaPayload(): array
    {
        if (!$this->hasHelp) {
            return [];
        }

        $media = [];
        $sortOrder = 0;

        foreach ($this->videoItems as $video) {
            $url = $this->nullableValue((string) ($video['url'] ?? ''));
            $name = $this->nullableValue((string) ($video['name'] ?? ''));
            $description = $this->nullableValue((string) ($video['description'] ?? ''));

            if (!$url) {
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

        foreach ($this->imageItems as $item) {
            $file = $item['file'] ?? null;
            $description = $this->nullableValue((string) ($item['description'] ?? ''));

            if (!$file instanceof TemporaryUploadedFile) {
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

        foreach ($this->pdfItems as $item) {
            $file = $item['file'] ?? null;
            $description = $this->nullableValue((string) ($item['description'] ?? ''));

            if (!$file instanceof TemporaryUploadedFile) {
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

    private function hasHelpContent(): bool
    {
        if ($this->nullableValue($this->helpText) !== null) {
            return true;
        }

        if (!empty($this->existingMedia)) {
            return true;
        }

        foreach ($this->videoItems as $video) {
            if ($this->nullableValue((string) ($video['url'] ?? '')) !== null) {
                return true;
            }
        }

        foreach ($this->imageItems as $item) {
            if (($item['file'] ?? null) instanceof TemporaryUploadedFile) {
                return true;
            }
        }

        foreach ($this->pdfItems as $item) {
            if (($item['file'] ?? null) instanceof TemporaryUploadedFile) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeInputs(): void
    {
        $this->name = $this->sanitizeText($this->name, 255);
        $this->description = $this->sanitizeText($this->description, 1000);
        $this->helpText = $this->sanitizeText($this->helpText, 2000);

        foreach ($this->videoItems as $index => $video) {
            $this->videoItems[$index]['url'] = $this->sanitizeUrl((string) ($video['url'] ?? ''));
            $this->videoItems[$index]['name'] = $this->sanitizeText((string) ($video['name'] ?? ''), 255);
            $this->videoItems[$index]['description'] = $this->sanitizeText((string) ($video['description'] ?? ''), 500);
        }

        foreach ($this->imageItems as $index => $item) {
            $this->imageItems[$index]['name'] = $this->sanitizeText((string) ($item['name'] ?? ''), 255);
            $this->imageItems[$index]['description'] = $this->sanitizeText((string) ($item['description'] ?? ''), 500);
        }

        foreach ($this->pdfItems as $index => $item) {
            $this->pdfItems[$index]['name'] = $this->sanitizeText((string) ($item['name'] ?? ''), 255);
            $this->pdfItems[$index]['description'] = $this->sanitizeText((string) ($item['description'] ?? ''), 500);
        }

        if ($this->value !== null && $this->value !== '') {
            $this->value = round((float) $this->value, 2);
        }
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
            'name' => trans('service-order::messages.procedure_name'),
            'description' => trans('service-order::messages.procedure_description'),
            'value' => trans('service-order::messages.procedure_value'),
            'hasHelp' => trans('service-order::messages.procedure_help_switch'),
            'helpText' => trans('service-order::messages.procedure_help_text'),
            'videoItems.*.url' => trans('service-order::messages.procedure_help_video'),
            'videoItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'videoItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'imageItems.*.file' => trans('service-order::messages.procedure_help_image'),
            'imageItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'imageItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'pdfItems.*.file' => trans('service-order::messages.procedure_help_pdf'),
            'pdfItems.*.name' => trans('service-order::messages.procedure_help_media_name'),
            'pdfItems.*.description' => trans('service-order::messages.procedure_help_media_text'),
            'newMediaType' => trans('service-order::messages.procedure_help_media_type'),
            'newMediaName' => trans('service-order::messages.procedure_help_media_name'),
            'newMediaDescription' => trans('service-order::messages.procedure_help_media_text'),
            'newMediaUrl' => trans('service-order::messages.procedure_help_video'),
            'newMediaFile' => trans('service-order::messages.procedure_help_media_file'),
        ];
    }

    private function resetNewMediaForm(): void
    {
        $this->newMediaType = 'image';
        $this->newMediaName = '';
        $this->newMediaDescription = '';
        $this->newMediaUrl = '';
        $this->newMediaFile = null;
        $this->resetValidation(['newMediaType', 'newMediaName', 'newMediaDescription', 'newMediaUrl', 'newMediaFile']);
    }

    private function newVideoMediaItem(): array
    {
        return [
            'url' => $this->newMediaUrl,
            'name' => $this->newMediaName,
            'description' => $this->newMediaDescription,
        ];
    }

    private function newFileMediaItem(): array
    {
        return [
            'file' => $this->newMediaFile,
            'name' => $this->newMediaName,
            'description' => $this->newMediaDescription,
        ];
    }

    public function render()
    {
        return view('service-order::livewire.procedure.procedure-management');
    }
}
