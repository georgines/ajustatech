<x-slot name="page_title">{{ $title }}</x-slot>
<div x-data="{
    previewMedia: {
        type: '',
        url: '',
        name: '',
    },
    openFullscreen(type, url, name = '') {
        this.previewMedia = { type, url, name };
    }
}">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ trans('service-order::messages.procedure_form_title') }}</h5>
            <a class="btn btn-label-secondary" href="{{ route('service-order-procedures-show') }}">
                {{ trans('service-order::messages.back_to_list') }}
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label">{{ trans('service-order::messages.procedure_name') }}</label>
                    <input class="form-control"
                        type="text"
                        maxlength="255"
                        wire:model.blur="name"
                        placeholder="{{ trans('service-order::messages.procedure_name_placeholder') }}">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label">{{ trans('service-order::messages.procedure_value') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input class="form-control"
                            type="number"
                            min="0"
                            max="999999.99"
                            step="0.01"
                            inputmode="decimal"
                            wire:model.blur="value">
                    </div>
                    @error('value') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">{{ trans('service-order::messages.procedure_description') }}</label>
                    <textarea class="form-control"
                        rows="3"
                        maxlength="1000"
                        wire:model.blur="description"
                        placeholder="{{ trans('service-order::messages.procedure_description_placeholder') }}"></textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('service-order::messages.procedure_help_title') }}</h5>
            <small class="text-muted">{{ trans('service-order::messages.procedure_help_subtitle') }}</small>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="switch">
                        <input wire:model.live="hasHelp" class="switch-input" type="checkbox" />
                        <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                        </span>
                        <span class="switch-label">{{ trans('service-order::messages.procedure_help_switch') }}</span>
                    </label>
                    @error('hasHelp') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">{{ trans('service-order::messages.procedure_help_text') }}</label>
                    <textarea class="form-control"
                        rows="3"
                        maxlength="2000"
                        wire:model.blur="helpText"
                        @disabled(!$hasHelp)
                        placeholder="{{ trans('service-order::messages.procedure_help_text_placeholder') }}"></textarea>
                    @error('helpText') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ trans('service-order::messages.procedure_help_existing_media') }}</h6>
                <button type="button"
                    class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#procedureAddMediaModal"
                    @disabled(!$hasHelp)>
                    {{ trans('service-order::messages.procedure_add_media') }}
                </button>
            </div>
            @error('newMediaType') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
            @error('newMediaName') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
            @error('newMediaDescription') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
            @error('newMediaUrl') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror
            @error('newMediaFile') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

            <div class="row g-3">
                @foreach ($existingMedia as $media)
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-label-primary text-uppercase">{{ $media['type'] }}</span>
                                <button type="button" class="btn btn-sm btn-icon" wire:click="removeExistingMedia('{{ $media['id'] }}')">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                            @if ($media['type'] === 'video')
                                <div class="small text-break">{{ $media['url'] }}</div>
                            @elseif ($media['type'] === 'image' && $media['public_url'])
                                <button type="button"
                                    class="btn p-0 border-0 bg-transparent"
                                    data-bs-toggle="modal"
                                    data-bs-target="#procedureManagementMediaFullscreenModal"
                                    x-on:click="openFullscreen('image', @js($media['public_url']), @js($media['display_name'] ?? 'Imagem'))">
                                    <img src="{{ $media['public_url'] }}" alt="media" class="img-fluid rounded border">
                                </button>
                            @elseif ($media['type'] === 'pdf' && $media['public_url'])
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#procedureManagementMediaFullscreenModal"
                                    x-on:click="openFullscreen('pdf', @js($media['public_url']), @js($media['display_name'] ?? 'PDF'))">
                                    {{ $media['display_name'] ?? 'PDF' }}
                                </button>
                            @endif
                            @if ($media['display_name'])
                                <div class="small fw-semibold mt-2">{{ $media['display_name'] }}</div>
                            @endif
                            @if ($media['description'])
                                <div class="small text-muted mt-2">{{ $media['description'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @foreach ($imageItems as $index => $item)
                    @if (($item['file'] ?? null) && method_exists($item['file'], 'temporaryUrl'))
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-label-info text-uppercase">image</span>
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="removeImageItem({{ $index }})">
                                        <i class="text-primary ti ti-trash"></i>
                                    </button>
                                </div>
                                <img src="{{ $item['file']->temporaryUrl() }}" alt="preview" class="img-fluid rounded border">
                                @if (!empty($item['name']))
                                    <div class="small fw-semibold mt-2">{{ $item['name'] }}</div>
                                @endif
                                @if (!empty($item['description']))
                                    <div class="small text-muted mt-2">{{ $item['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach

                @foreach ($pdfItems as $index => $item)
                    @if (($item['file'] ?? null))
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-label-info text-uppercase">pdf</span>
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="removePdfItem({{ $index }})">
                                        <i class="text-primary ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="small">{{ $item['file']->getClientOriginalName() }}</div>
                                @if (!empty($item['name']))
                                    <div class="small fw-semibold mt-2">{{ $item['name'] }}</div>
                                @endif
                                @if (!empty($item['description']))
                                    <div class="small text-muted mt-2">{{ $item['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach

                @foreach ($videoItems as $index => $item)
                    @if (!empty($item['url']))
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-label-info text-uppercase">video</span>
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="removeVideoItem({{ $index }})">
                                        <i class="text-primary ti ti-trash"></i>
                                    </button>
                                </div>
                                <div class="small text-break">{{ $item['url'] }}</div>
                                @if (!empty($item['name']))
                                    <div class="small fw-semibold mt-2">{{ $item['name'] }}</div>
                                @endif
                                @if (!empty($item['description']))
                                    <div class="small text-muted mt-2">{{ $item['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ $mode === 'edit' ? trans('service-order::messages.update') : trans('service-order::messages.save') }}
        </button>
    </div>

    <div wire:ignore.self class="modal fade" id="procedureManagementMediaFullscreenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="previewMedia.name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-dark">
                    <template x-if="previewMedia.type === 'image'">
                        <div class="h-100 d-flex justify-content-center align-items-center">
                            <img :src="previewMedia.url" :alt="previewMedia.name" class="img-fluid" style="max-height: 92vh;">
                        </div>
                    </template>
                    <template x-if="previewMedia.type === 'pdf'">
                        <div class="h-100">
                            <iframe :src="previewMedia.url" :title="previewMedia.name" class="w-100 h-100 border-0"></iframe>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="procedureAddMediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.procedure_add_media') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">{{ trans('service-order::messages.procedure_help_media_type') }}</label>
                            <select class="form-select" wire:model.live="newMediaType">
                                <option value="image">{{ trans('service-order::messages.procedure_help_images') }}</option>
                                <option value="video">{{ trans('service-order::messages.procedure_help_videos') }}</option>
                                <option value="pdf">{{ trans('service-order::messages.procedure_help_pdfs') }}</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">{{ trans('service-order::messages.procedure_help_media_name') }}</label>
                            <input class="form-control"
                                type="text"
                                maxlength="255"
                                wire:model.blur="newMediaName"
                                placeholder="{{ trans('service-order::messages.procedure_help_media_name_placeholder') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ trans('service-order::messages.procedure_help_media_text') }}</label>
                            <input class="form-control"
                                type="text"
                                maxlength="500"
                                wire:model.blur="newMediaDescription"
                                placeholder="{{ trans('service-order::messages.procedure_help_media_text_placeholder') }}">
                        </div>

                        @if ($newMediaType === 'video')
                            <div class="col-12">
                                <label class="form-label">{{ trans('service-order::messages.procedure_help_video') }}</label>
                                <input class="form-control"
                                    type="url"
                                    maxlength="1000"
                                    wire:model.blur="newMediaUrl"
                                    placeholder="https://...">
                            </div>
                        @elseif ($newMediaType === 'image')
                            <div class="col-12">
                                <label class="form-label">{{ trans('service-order::messages.procedure_help_image') }}</label>
                                <input class="form-control" type="file" accept="image/*" wire:model="newMediaFile">
                            </div>
                        @else
                            <div class="col-12">
                                <label class="form-label">{{ trans('service-order::messages.procedure_help_pdf') }}</label>
                                <input class="form-control" type="file" accept="application/pdf" wire:model="newMediaFile">
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ trans('service-order::messages.cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="addMediaItem">{{ trans('service-order::messages.add') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('procedure-media-added', () => {
                const modalEl = document.getElementById('procedureAddMediaModal');
                if (!modalEl) return;
                const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
                instance.hide();
            });
        });
    </script>
@endscript
