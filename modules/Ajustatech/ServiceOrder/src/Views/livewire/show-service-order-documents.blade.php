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
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Slug</th>
                        <th>Conteudo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>{{ $document['name'] }}</td>
                            <td>{{ $document['slug'] }}</td>
                            <td>{{ $document['value'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Nenhum documento cadastrado para este tipo de equipamento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

