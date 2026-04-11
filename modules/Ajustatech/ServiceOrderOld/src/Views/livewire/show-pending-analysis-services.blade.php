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
                        <th>Progresso</th>
                        <th>Status</th>
                        <th class="text-end">Acao</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($analysisServices as $analysis)
                        @php
                            $total = (int) ($analysis->total_questions_count ?? 0);
                            $answered = min((int) ($analysis->answered_questions_count ?? 0), $total);
                            $percent = $total > 0 ? (int) floor(($answered / $total) * 100) : 0;
                            $orderNumber = $analysis->order?->order_number;
                        @endphp
                        <tr>
                            <td>{{ $orderNumber ? 'OS #' . str_pad((string) $orderNumber, 6, '0', STR_PAD_LEFT) : '-' }}</td>
                            <td>{{ $analysis->order?->customer_name ?: '-' }}</td>
                            <td>{{ $analysis->order?->equipment_name ?: '-' }}</td>
                            <td>{{ data_get($analysis->analysis_type_snapshot, 'name', $analysis->analysisType?->name) }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ $percent }}%</small>
                                </div>
                            </td>
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
                            <td colspan="7" class="text-center text-muted">Nenhum servico de analise pendente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
