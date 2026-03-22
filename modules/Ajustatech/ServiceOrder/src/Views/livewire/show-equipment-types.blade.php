<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('service-order-equipment-types-create') }}">Novo tipo de equipamento</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Status</th>
                        <th>Quantidade de Campos</th>
                        <th class="text-end">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipmentTypes as $equipmentType)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $equipmentType->name }}</div>
                                @if ($equipmentType->description)
                                    <small class="text-muted">{{ $equipmentType->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $equipmentType->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $equipmentType->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>{{ $equipmentType->fields_count }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-icon btn-text-secondary rounded-pill me-1"
                                   href="{{ route('service-order-equipment-types-edit', ['id' => $equipmentType->id]) }}"
                                   title="Editar"
                                   aria-label="Editar">
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                                        type="button"
                                        wire:click="toggleStatus('{{ $equipmentType->id }}')"
                                        title="Ativar/Inativar"
                                        aria-label="Ativar/Inativar">
                                    <i class="ti ti-power"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum tipo de equipamento cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
