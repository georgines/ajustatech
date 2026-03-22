<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('service-order-orders-create') }}">Nova ordem de servico</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Equipamento</th>
                        <th>Servicos</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $hasDocuments = collect($order->fields_snapshot ?? [])
                                ->contains(fn ($field) => ($field['field_type'] ?? null) === 'document');
                        @endphp
                        <tr>
                            <td>{{ optional($order->entry_date)->format('d/m/Y') }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->equipment_name }}</td>
                            <td>{{ $order->serviceItems->count() }}</td>
                            <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                   href="{{ route('service-order-orders-edit', ['id' => $order->id]) }}"
                                   title="Editar ordem">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                   href="{{ route('service-order-orders-documents', ['id' => $order->id]) }}"
                                   @if (!$hasDocuments) aria-disabled="true" @endif
                                   @class([
                                       'disabled pe-none opacity-50' => !$hasDocuments,
                                   ])
                                   title="Ver documentos">
                                    <i class="ti ti-file-text"></i>
                                </a>
                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger"
                                        type="button"
                                        wire:click="confirmCancelOrder('{{ $order->id }}')"
                                        @disabled($order->status === 'canceled')
                                        title="Cancelar ordem">
                                    <i class="ti ti-ban"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhuma ordem de servico cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
