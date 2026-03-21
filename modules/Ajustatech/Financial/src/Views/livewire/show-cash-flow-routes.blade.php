<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('financial-cash-routes-create') }}">{{ trans('financial::messages.cash_route_new') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('financial::messages.cash_route_flow') }}</th>
                        <th>{{ trans('financial::messages.payment_method_type') }}</th>
                        <th>{{ trans('financial::messages.managerial_cash') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($routes as $route)
                        <tr>
                            <td>{{ $this->flowLabel($route->flow_key) }}</td>
                            <td>{{ $route->payment_method_type ?: '-' }}</td>
                            <td>{{ $route->companyCash?->cash_name }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                    href="{{ route('financial-cash-routes-edit', ['id' => $route->id]) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('financial::messages.edit') }}"
                                    aria-label="{{ trans('financial::messages.edit') }}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger"
                                    wire:click="confirmDelete('{{ $route->id }}')"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('financial::messages.delete') }}"
                                    aria-label="{{ trans('financial::messages.delete') }}">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ trans('financial::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
