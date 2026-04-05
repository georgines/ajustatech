<x-slot name="page_title">{{ $title }}</x-slot>
<div>
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

            @if (!empty($existingMedia))
                <hr class="my-4">
                <h6>{{ trans('service-order::messages.procedure_help_existing_media') }}</h6>
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
                                    <img src="{{ $media['public_url'] }}" alt="media" class="img-fluid rounded border">
                                @elseif ($media['type'] === 'pdf' && $media['public_url'])
                                    <a href="{{ $media['public_url'] }}" target="_blank" class="small">{{ $media['original_name'] ?? 'PDF' }}</a>
                                @endif
                                @if ($media['description'])
                                    <div class="small text-muted mt-2">{{ $media['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ trans('service-order::messages.procedure_help_videos') }}</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addVideoItem" @disabled(!$hasHelp)>
                    {{ trans('service-order::messages.add') }}
                </button>
            </div>
            <div class="row g-3">
                @foreach ($videoItems as $index => $video)
                    <div class="col-12">
                        <div class="row g-2 align-items-start">
                            <div class="col-12 col-lg-5">
                                <input class="form-control"
                                    type="url"
                                    maxlength="1000"
                                    wire:model.blur="videoItems.{{ $index }}.url"
                                    @disabled(!$hasHelp)
                                    placeholder="https://...">
                                @error('videoItems.' . $index . '.url') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-6">
                                <input class="form-control"
                                    type="text"
                                    maxlength="500"
                                    wire:model.blur="videoItems.{{ $index }}.description"
                                    @disabled(!$hasHelp)
                                    placeholder="{{ trans('service-order::messages.procedure_help_media_text_placeholder') }}">
                                @error('videoItems.' . $index . '.description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-1">
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="removeVideoItem({{ $index }})"
                                    @disabled(!$hasHelp || count($videoItems) === 1)>
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ trans('service-order::messages.procedure_help_images') }}</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addImageItem" @disabled(!$hasHelp)>
                    {{ trans('service-order::messages.add') }}
                </button>
            </div>
            <div class="row g-3">
                @foreach ($imageItems as $index => $item)
                    <div class="col-12">
                        <div class="row g-2 align-items-start">
                            <div class="col-12 col-lg-5">
                                <input class="form-control"
                                    type="file"
                                    accept="image/*"
                                    wire:model="imageItems.{{ $index }}.file"
                                    @disabled(!$hasHelp)>
                                @error('imageItems.' . $index . '.file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-6">
                                <input class="form-control"
                                    type="text"
                                    maxlength="500"
                                    wire:model.blur="imageItems.{{ $index }}.description"
                                    @disabled(!$hasHelp)
                                    placeholder="{{ trans('service-order::messages.procedure_help_media_text_placeholder') }}">
                                @error('imageItems.' . $index . '.description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-1">
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="removeImageItem({{ $index }})"
                                    @disabled(!$hasHelp || count($imageItems) === 1)>
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">{{ trans('service-order::messages.procedure_help_pdfs') }}</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPdfItem" @disabled(!$hasHelp)>
                    {{ trans('service-order::messages.add') }}
                </button>
            </div>
            <div class="row g-3">
                @foreach ($pdfItems as $index => $item)
                    <div class="col-12">
                        <div class="row g-2 align-items-start">
                            <div class="col-12 col-lg-5">
                                <input class="form-control"
                                    type="file"
                                    accept="application/pdf"
                                    wire:model="pdfItems.{{ $index }}.file"
                                    @disabled(!$hasHelp)>
                                @error('pdfItems.' . $index . '.file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-6">
                                <input class="form-control"
                                    type="text"
                                    maxlength="500"
                                    wire:model.blur="pdfItems.{{ $index }}.description"
                                    @disabled(!$hasHelp)
                                    placeholder="{{ trans('service-order::messages.procedure_help_media_text_placeholder') }}">
                                @error('pdfItems.' . $index . '.description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-lg-1">
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="removePdfItem({{ $index }})"
                                    @disabled(!$hasHelp || count($pdfItems) === 1)>
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ $mode === 'edit' ? trans('service-order::messages.update') : trans('service-order::messages.save') }}
        </button>
    </div>
</div>

