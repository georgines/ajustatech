<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-1">Ordem de Servico</h5>
            <div class="text-muted">Cliente: {{ $order->customer_name }}</div>
            <div class="text-muted">Equipamento: {{ $order->equipment_name }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (empty($documents))
                <div class="text-center text-muted">
                    Nenhum documento cadastrado para este tipo de equipamento.
                </div>
            @else
                <div class="row g-3">
                    @foreach ($documents as $document)
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-1">{{ $document['name'] }}</div>
                                <small class="text-muted d-block mb-2">{{ $document['slug'] }}</small>
                                <div class="text-body">{{ $document['value'] ?: '-' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
