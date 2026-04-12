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

                <div class="col-12">
                    <label class="form-label d-block">{{ trans('settings::messages.company_open_days') }}</label>
                    <div class="row g-2">
                        @foreach ($dayOptions as $option)
                            <div class="col-6 col-md-3">
                                <label class="switch mb-0">
                                    <input class="switch-input" type="checkbox" value="{{ $option['value'] }}" wire:model.live="workingDays" />
                                    <span class="switch-toggle-slider">
                                        <span class="switch-on"></span>
                                        <span class="switch-off"></span>
                                    </span>
                                    <span class="switch-label">{{ $option['label'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('workingDays') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="form-label mb-0">{{ trans('settings::messages.company_holidays') }}</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addHolidayDate">
                            {{ trans('settings::messages.add_holiday_date') }}
                        </button>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @forelse ($holidayDates as $index => $holidayDate)
                            <div class="d-flex align-items-center gap-2">
                                <input
                                    class="form-control"
                                    type="date"
                                    wire:model.blur="holidayDates.{{ $index }}"
                                    max="2100-12-31"
                                    min="2000-01-01">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="removeHolidayDate({{ $index }})"
                                    title="{{ trans('settings::messages.delete') }}"
                                    aria-label="{{ trans('settings::messages.delete') }}">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                            @error('holidayDates.'.$index) <small class="text-danger d-block">{{ $message }}</small> @enderror
                        @empty
                            <div class="text-muted">{{ trans('settings::messages.no_holidays_registered') }}</div>
                        @endforelse
                    </div>
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
            <div class="d-flex flex-wrap gap-2">
                @foreach ($statusFlows as $flow)
                    <span class="badge bg-label-secondary">{{ $flow['name'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ trans('settings::messages.save') }}
        </button>
    </div>
</div>


