<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('settings-service-order-edit') }}">
            <i class="ti ti-settings me-1"></i>{{ trans('settings::messages.edit_settings') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.service_order_settings_summary') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="border rounded-2 p-3 h-100">
                        <small class="text-muted d-block">{{ trans('settings::messages.service_order_initial_number') }}</small>
                        <h4 class="mb-0">{{ number_format($initialOrderNumber, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="border rounded-2 p-3 h-100">
                        <small class="text-muted d-block mb-2">{{ trans('settings::messages.company_open_days') }}</small>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($workingDays as $day)
                                <span class="badge bg-label-primary">{{ $dayLabels[$day] ?? $day }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <small class="text-muted d-block mb-2">{{ trans('settings::messages.company_holidays') }}</small>
                @if (count($holidayDates) > 0)
                    <div class="d-flex flex-column gap-2">
                        @foreach ($holidayDates as $date)
                            <span class="badge bg-label-secondary text-start">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">{{ trans('settings::messages.no_holidays_registered') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ trans('settings::messages.service_order_status_flow_title') }}</h5>
            <small class="text-muted">{{ trans('settings::messages.service_order_status_flow_auto_hint') }}</small>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column gap-2">
                @foreach ($statusFlows as $flow)
                    <div class="d-flex align-items-center justify-content-between border rounded-2 p-2">
                        <div>
                            <div class="fw-semibold">{{ $flow['sort_order'] }}. {{ $flow['name'] }}</div>
                            <small class="text-muted">{{ $flow['code'] }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            @if ($flow['is_default_initial'])
                                <span class="badge bg-label-primary">{{ trans('settings::messages.status_flow_initial') }}</span>
                            @endif
                            @if ($flow['is_terminal'])
                                <span class="badge bg-label-secondary">{{ trans('settings::messages.status_flow_terminal') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>


