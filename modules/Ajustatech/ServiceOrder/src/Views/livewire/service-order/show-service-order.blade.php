<x-slot name="page_title">{{ $title }}</x-slot>

<div
    x-data="{
        confirmDelete(id) {
            const runDelete = () => $wire.deleteServiceOrder(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: @js(trans('service-order::messages.service_order_confirm_delete')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runDelete();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.service_order_confirm_delete')))) {
                runDelete();
            }
        },
        confirmDuplicate(id) {
            const runDuplicate = () => $wire.duplicateServiceOrder(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: @js(trans('service-order::messages.service_order_confirm_duplicate')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runDuplicate();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.service_order_confirm_duplicate')))) {
                runDuplicate();
            }
        }
    }"
>
    <div class="card mb-3">
        <div class="card-header header-elements">
            <span class="me-2">{{ trans('service-order::messages.filters') }}</span>
            <div class="card-header-elements ms-auto">
                <button type="button" class="btn btn-primary" x-on:click="$dispatch('service-order-create-wizard-open')">
                    <span class="tf-icon ti ti-plus ti-xs me-1"></span>{{ trans('service-order::messages.new_service_order') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="soLimit">{{ trans('service-order::messages.records_per_page') }}</label>
                    <select id="soLimit" class="form-select" wire:model.live="limitePerPage">
                        <option value="10">10</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="soStatus">{{ trans('service-order::messages.status') }}</label>
                    <select id="soStatus" class="form-select" wire:model.live="statusFlowId">
                        <option value="">{{ trans('service-order::messages.status_all') }}</option>
                        @foreach ($statusFlows as $statusFlow)
                            <option value="{{ $statusFlow->id }}">{{ $statusFlow->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="openedFrom">{{ trans('service-order::messages.opened_from') }}</label>
                    <input id="openedFrom" type="date" class="form-control" wire:model.live="openedFrom">
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="openedTo">{{ trans('service-order::messages.opened_to') }}</label>
                    <input id="openedTo" type="date" class="form-control" wire:model.live="openedTo">
                </div>

                <div class="col-12">
                    <label class="form-label" for="soSearch">{{ trans('service-order::messages.search') }}</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input
                            id="soSearch"
                            type="text"
                            class="form-control"
                            placeholder="{{ trans('service-order::messages.service_order_search_placeholder') }}"
                            wire:model.live.debounce.400ms="search"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ trans('service-order::messages.so_number') }}</th>
                        <th>{{ trans('service-order::messages.so_days') }}</th>
                        <th>{{ trans('service-order::messages.so_opened_at') }}</th>
                        <th>{{ trans('service-order::messages.so_customer') }}</th>
                        <th>{{ trans('service-order::messages.so_status') }}</th>
                        <th>{{ trans('service-order::messages.so_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceOrders as $serviceOrder)
                        <tr>
                            <td>#{{ $serviceOrder['order_number'] }}</td>
                            <td>{{ $serviceOrder['business_days'] }}</td>
                            <td>{{ $serviceOrder['opened_at']?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $serviceOrder['customer_name'] }}</td>
                            <td>
                                <span class="badge bg-label-info">{{ $serviceOrder['status_name'] }}</span>
                            </td>
                            <td>
                                <a
                                    class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-edit', ['serviceOrder' => $serviceOrder['id']]) }}"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}"
                                >
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDuplicate('{{ $serviceOrder['id'] }}')"
                                    title="{{ trans('service-order::messages.duplicate') }}"
                                    aria-label="{{ trans('service-order::messages.duplicate') }}"
                                >
                                    <i class="text-primary ti ti-copy"></i>
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDelete('{{ $serviceOrder['id'] }}')"
                                    title="{{ trans('service-order::messages.delete') }}"
                                    aria-label="{{ trans('service-order::messages.delete') }}"
                                >
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ trans('service-order::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer pt-3">
            {{ $serviceOrders->links('service-order::vendor.pagination.vuexy-bootstrap-5') }}
        </div>
    </div>

    @livewire('service-order-create-wizard')
</div>
