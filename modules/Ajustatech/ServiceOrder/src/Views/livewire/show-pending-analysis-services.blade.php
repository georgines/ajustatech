<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ordem</th>
                        <th>Cliente</th>
                        <th>Equipamento</th>
                        <th>Tipo de Analise</th>
                        <th>Status</th>
                        <th class="text-end">Acao</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analysisServices as $analysis)
                        <tr>
                            <td>#{{ $analysis->order?->id ? substr($analysis->order->id, 0, 8) : '-' }}</td>
                            <td>{{ $analysis->order?->customer_name ?: '-' }}</td>
                            <td>{{ $analysis->order?->equipment_name ?: '-' }}</td>
                            <td>{{ data_get($analysis->analysis_type_snapshot, 'name', $analysis->analysisType?->name) }}</td>
                            <td>{{ $statusLabels[$analysis->status] ?? $analysis->status }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-primary"
                                   href="{{ route('service-order-analysis-execution-start', ['id' => $analysis->id]) }}">
                                    {{ $analysis->status === 'pending' ? 'Comecar analise' : 'Continuar analise' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum servico de analise pendente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

