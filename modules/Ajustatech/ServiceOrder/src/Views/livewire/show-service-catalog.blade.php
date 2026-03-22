<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('service-order-services-create') }}">Novo servico</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Servico</th>
                        <th>Valor base</th>
                        <th>Status</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $service->name }}</div>
                                @if ($service->description)
                                    <small class="text-muted">{{ $service->description }}</small>
                                @endif
                            </td>
                            <td>R$ {{ number_format((float) $service->base_price, 2, ',', '.') }}</td>
                            <td>{{ $service->is_active ? 'Ativo' : 'Inativo' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                   href="{{ route('service-order-services-edit', ['id' => $service->id]) }}">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                                        type="button"
                                        wire:click="toggleStatus('{{ $service->id }}')">
                                    <i class="ti ti-power"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum servico cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
