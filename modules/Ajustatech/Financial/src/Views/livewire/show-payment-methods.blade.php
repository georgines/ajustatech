<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('financial-payment-methods-create') }}">{{ trans('financial::messages.payment_method_new') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('financial::messages.payment_method_type') }}</th>
                        <th>{{ trans('financial::messages.payment_method_name') }}</th>
                        <th>{{ trans('financial::messages.status') }}</th>
                        <th>{{ trans('financial::messages.payment_method_costs') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($methods as $method)
                        <tr>
                            <td>{{ $method->type }}</td>
                            <td>{{ $method->name }}</td>
                            <td>{{ $method->is_active ? 'ativo' : 'inativo' }}</td>
                            <td>
                                @foreach ($method->costs as $cost)
                                    <div class="small mb-1">
                                        Fixo: R$ {{ number_format((float) $cost->fixed_cost, 2, ',', '.') }} |
                                        %: {{ number_format((float) $cost->percent_cost, 2, ',', '.') }}
                                        @if ($cost->brand)
                                            | {{ trans('financial::messages.payment_method_brand') }}: {{ $cost->brand }}
                                        @endif
                                        @if ($cost->installments)
                                            | {{ trans('financial::messages.payment_method_installments') }}: {{ $cost->installments }}
                                        @endif
                                        @if ($cost->receipt_channel)
                                            | {{ trans('financial::messages.payment_method_receipt_channel') }}: {{ $cost->receipt_channel }}
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                    href="{{ route('financial-payment-methods-edit', ['id' => $method->id]) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('financial::messages.edit') }}"
                                    aria-label="{{ trans('financial::messages.edit') }}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger"
                                    wire:click="confirmDelete('{{ $method->id }}')"
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
                            <td colspan="5" class="text-center">{{ trans('financial::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
