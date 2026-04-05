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
            <div class="row g-3 mb-1">
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

                <div class="col-12 col-lg-6">
                    <label class="form-label">{{ trans('service-order::messages.procedure_help_image') }}</label>
                    <input class="form-control"
                        type="url"
                        maxlength="1000"
                        wire:model.blur="helpImageUrl"
                        @disabled(!$hasHelp)
                        placeholder="https://...">
                    @error('helpImageUrl') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">{{ trans('service-order::messages.procedure_help_video') }}</label>
                    <input class="form-control"
                        type="url"
                        maxlength="1000"
                        wire:model.blur="helpVideoUrl"
                        @disabled(!$hasHelp)
                        placeholder="https://...">
                    @error('helpVideoUrl') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ $mode === 'edit' ? trans('service-order::messages.update') : trans('service-order::messages.save') }}
        </button>
    </div>
</div>
