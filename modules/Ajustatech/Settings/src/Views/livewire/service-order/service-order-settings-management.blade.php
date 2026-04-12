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
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openHolidayModal">
                            {{ trans('settings::messages.add_holiday_date') }}
                        </button>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @forelse ($holidays as $index => $holiday)
                            @php
                                $holidayDate = (string) ($holiday['date'] ?? '');
                                $formattedDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $holidayDate) === 1
                                    ? \Carbon\Carbon::parse($holidayDate)->format('d/m/Y')
                                    : $holidayDate;
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <input class="form-control" type="text" value="{{ $holiday['name'] }}" readonly>
                                <input class="form-control" type="text" value="{{ $formattedDate }}" readonly>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="removeHoliday({{ $index }})"
                                    title="{{ trans('settings::messages.delete') }}"
                                    aria-label="{{ trans('settings::messages.delete') }}">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                            @error('holidays.'.$index.'.name') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            @error('holidays.'.$index.'.date') <small class="text-danger d-block">{{ $message }}</small> @enderror
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
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ trans('settings::messages.save') }}
        </button>
    </div>

    @if ($isHolidayModalOpen)
        <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true" wire:click.self="closeHolidayModal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('settings::messages.holiday_modal_title') }}</h5>
                        <button type="button" class="btn-close" aria-label="{{ trans('settings::messages.close') }}" wire:click="closeHolidayModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('settings::messages.holiday_name_label') }}</label>
                            <input type="text" class="form-control" maxlength="100" wire:model.defer="holidayName" placeholder="{{ trans('settings::messages.holiday_name_placeholder') }}">
                            @error('holidayName') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ trans('settings::messages.holiday_date_label') }}</label>
                            <input type="date" class="form-control" min="2000-01-01" max="2100-12-31" wire:model.defer="holidayDate">
                            @error('holidayDate') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeHolidayModal">{{ trans('settings::messages.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="addHoliday">{{ trans('settings::messages.add') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>


