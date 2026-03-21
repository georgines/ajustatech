<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('financial-receivables-create') }}">{{ trans('financial::messages.receivable_new') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('financial::messages.counterparty') }}</th>
                        <th>{{ trans('financial::messages.amount') }}</th>
                        <th>{{ trans('financial::messages.due_date') }}</th>
                        <th>{{ trans('financial::messages.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivables as $item)
                        <tr>
                            <td>{{ $item->counterparty_name }}</td>
                            <td>R$ {{ number_format((float) $item->amount, 2, ',', '.') }}</td>
                            <td>{{ optional($item->due_date)->format('d/m/Y') }}</td>
                            <td>{{ $item->status }}</td>
                            <td>
                                @if ($item->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-success" wire:click="settle('{{ $item->id }}')">
                                        {{ trans('financial::messages.mark_as_received') }}
                                    </button>
                                @endif
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