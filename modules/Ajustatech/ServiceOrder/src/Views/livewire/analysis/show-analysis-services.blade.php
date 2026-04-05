<x-slot name="page_title">{{ $title }}</x-slot>

<div
    x-data="{
        confirmDelete(id) {
            const runDelete = () => $wire.deleteAnalysisService(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: @js(trans('service-order::messages.analysis_confirm_delete')),
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

            if (confirm(@js(trans('service-order::messages.analysis_confirm_delete')))) {
                runDelete();
            }
        }
    }"
>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('service-order-analyses-create') }}">
            {{ trans('service-order::messages.new_analysis_service') }}
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>{{ trans('service-order::messages.analysis_service_name') }}</th>
                        <th>{{ trans('service-order::messages.analysis_service_value') }}</th>
                        <th>{{ trans('service-order::messages.analysis_questions_count') }}</th>
                        <th>{{ trans('service-order::messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analysisServices as $analysis)
                        <tr>
                            <td>{{ $analysis['name'] }}</td>
                            <td>R$ {{ number_format((float) $analysis['value'], 2, ',', '.') }}</td>
                            <td>{{ $analysis['questions_count'] }}</td>
                            <td>
                                <a class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-analyses-edit', ['id' => $analysis['id']]) }}"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDelete('{{ $analysis['id'] }}')"
                                    title="{{ trans('service-order::messages.delete') }}"
                                    aria-label="{{ trans('service-order::messages.delete') }}">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ trans('service-order::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

