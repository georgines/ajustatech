<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('financial-card-brands-create') }}">{{ trans('financial::messages.card_brand_new') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('financial::messages.payment_method_brand') }}</th>
                        <th>{{ trans('financial::messages.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($brands as $brand)
                        <tr>
                            <td>{{ $brand->name }}</td>
                            <td>{{ $brand->is_active ? 'ativo' : 'inativo' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                    href="{{ route('financial-card-brands-edit', ['id' => $brand->id]) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('financial::messages.edit') }}"
                                    aria-label="{{ trans('financial::messages.edit') }}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger"
                                    wire:click="confirmDelete('{{ $brand->id }}')"
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
                            <td colspan="3" class="text-center">{{ trans('financial::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>