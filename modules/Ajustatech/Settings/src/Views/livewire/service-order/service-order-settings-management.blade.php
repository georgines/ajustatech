<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.service_order_settings_form_title') }}</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">{{ trans('settings::messages.service_order_initial_number') }}</label>
                    <input
                        class="form-control"
                        type="number"
                        min="1"
                        max="999999999"
                        step="1"
                        inputmode="numeric"
                        wire:model.blur="initialOrderNumber">
                    @error('initialOrderNumber') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.service_order_status_flow_title') }}</h5>
            <small class="text-muted">{{ trans('settings::messages.service_order_status_flow_auto_hint') }}</small>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-2">
                @foreach ($statusFlows as $flow)
                    <div class="border rounded-2 p-2">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="fw-semibold">{{ $flow['name'] }}</div>
                            <div class="d-flex align-items-center gap-1">
                                @if ($flow['is_default_initial'])
                                    <span class="badge bg-label-primary">{{ trans('settings::messages.status_flow_initial') }}</span>
                                @endif
                                @if ($flow['is_terminal'])
                                    <span class="badge bg-label-secondary">{{ trans('settings::messages.status_flow_terminal') }}</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">{{ $flow['description'] }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
            {{ trans('settings::messages.save') }}
        </button>
    </div>
</div>


