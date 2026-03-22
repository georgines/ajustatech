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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ optional($order->entry_date)->format('d/m/Y') }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->equipment_name }}</td>
                            <td>{{ $order->serviceItems->count() }}</td>
                            <td>{{ $order->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhuma ordem de servico cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
