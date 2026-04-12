<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.company_settings_form_title') }}</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_name_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="companyName">
                    @error('companyName') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_cnpj_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="cnpj" inputmode="numeric">
                    @error('cnpj') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_phone_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="phone">
                    @error('phone') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_email_label') }}</label>
                    <input class="form-control" type="email" wire:model.blur="email">
                    @error('email') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">{{ trans('settings::messages.company_address_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="addressLine">
                    @error('addressLine') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_neighborhood_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="neighborhood">
                    @error('neighborhood') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-8 col-md-4">
                    <label class="form-label">{{ trans('settings::messages.company_city_label') }}</label>
                    <input class="form-control" type="text" wire:model.blur="city">
                    @error('city') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-4 col-md-2">
                    <label class="form-label">{{ trans('settings::messages.company_state_label') }}</label>
                    <input class="form-control text-uppercase" type="text" maxlength="2" wire:model.blur="state">
                    @error('state') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.company_logo_title') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-md-6">
                    <label class="form-label">{{ trans('settings::messages.company_logo_input_label') }}</label>
                    <input class="form-control" type="file" wire:model="logo" accept="{{ $logoAccept }}">
                    <small class="text-muted d-block mt-1">{{ trans('settings::messages.company_logo_accept_hint') }}: {{ $logoAccept }}</small>
                    @error('logo') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-6">
                    @if ($temporaryLogoUrl)
                        <small class="text-muted d-block mb-2">{{ trans('settings::messages.company_logo_preview_new') }}</small>
                        <img src="{{ $temporaryLogoUrl }}" alt="Preview logo" class="rounded border" width="96" height="96">
                    @elseif ($currentLogoUrl)
                        <small class="text-muted d-block mb-2">{{ trans('settings::messages.company_logo_preview_current') }}</small>
                        <img src="{{ $currentLogoUrl }}" alt="Logo atual" class="rounded border" width="96" height="96">
                    @else
                        <small class="text-muted d-block">{{ trans('settings::messages.company_logo_preview_empty') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ trans('settings::messages.save') }}
        </button>
    </div>
</div>
