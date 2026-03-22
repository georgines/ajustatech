<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Etapa 1: Cliente</h5>
            <span class="badge {{ $customer_id ? 'bg-label-success' : 'bg-label-warning' }}">
                {{ $customer_id ? 'Cliente selecionado' : 'Selecione um cliente' }}
            </span>
        </div>
        <div class="card-body">
            @error('customer') <div class="alert alert-danger">{{ $message }}</div> @enderror
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label">Consultar cliente existente</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="customerSearch" placeholder="Digite nome, CPF/CNPJ, email...">
                    @if (!empty($customerResults))
                        <div class="list-group mt-2">
                            @foreach ($customerResults as $item)
                                <button type="button" class="list-group-item list-group-item-action" wire:click="selectCustomer('{{ $item['id'] }}')">
                                    <strong>{{ $item['name'] }}</strong>
                                    <span class="text-muted"> | {{ $item['cpf_cnpj'] }} | {{ $item['email'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif (mb_strlen(trim($customerSearch)) >= 2)
                        <small class="text-muted d-block mt-2">Cliente nao encontrado. Clique em "Cadastrar cliente" para criar antes de prosseguir.</small>
                    @endif
                </div>
                <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="clearSelectedCustomer">Limpar</button>
                    <button type="button" class="btn btn-outline-primary" wire:click="openCustomerModal">Cadastrar cliente</button>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" wire:click="proceedToOrder">Criar nova ordem</button>
            </div>
        </div>
    </div>

    @if ($currentStep === 'order')
        <div class="card mb-4">
            <div class="card-header"><h5 class="m-0">Etapa 2: Dados da ordem</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Tipo de equipamento</label>
                        <select class="form-select" wire:model.live="equipment_type_id">
                            <option value="">Selecione</option>
                            @foreach ($availableEquipmentTypes as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Equipamento</label>
                        <input class="form-control" wire:model="equipment_name" type="text">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Data de entrada</label>
                        <input class="form-control" wire:model="entry_date" type="date">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Marca</label>
                        <input class="form-control" wire:model="brand" type="text">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Modelo</label>
                        <input class="form-control" wire:model="model" type="text">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Numero de serie</label>
                        <input class="form-control" wire:model="serial_number" type="text">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Defeito relatado</label>
                        <input class="form-control" wire:model="reported_issue" type="text">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h5 class="m-0">Campos dinamicos do tipo de equipamento</h5></div>
            <div class="card-body">
                @forelse ($activeFieldSnapshots as $field)
                    @php
                        $slug = $field['slug'];
                        $type = $field['field_type'];
                    @endphp
                    <div class="mb-3">
                        <label class="form-label">
                            {{ $field['name'] }}
                            @if ($field['is_required']) <span class="text-danger">*</span> @endif
                            @if (!$field['is_printable']) <small class="text-muted">(interno)</small> @endif
                        </label>

                        @if ($type === 'text' || $type === 'document')
                            <textarea class="form-control" rows="{{ $type === 'document' ? 4 : 2 }}" wire:model="fieldValues.{{ $slug }}"></textarea>
                        @elseif ($type === 'select')
                            <select class="form-select" wire:model="fieldValues.{{ $slug }}">
                                <option value="">Selecione</option>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'radio')
                            <div>
                                @foreach (($field['options'] ?? []) as $option)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" wire:model="fieldValues.{{ $slug }}" value="{{ $option['value'] }}" id="{{ $slug }}_{{ $option['value'] }}">
                                        <label class="form-check-label" for="{{ $slug }}_{{ $option['value'] }}">{{ $option['label'] }}</label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif ($type === 'photo')
                            <input class="form-control" type="file" wire:model="uploadedFiles.{{ $slug }}" multiple accept="image/*">
                        @elseif ($type === 'file')
                            <input class="form-control" type="file" wire:model="uploadedFiles.{{ $slug }}" multiple>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Selecione um tipo de equipamento para carregar os campos dinamicos.</p>
                @endforelse
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0">Servicos a realizar</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openServiceModal">Cadastrar servico</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="includeAllRegisteredServices">Adicionar todos cadastrados</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label">Adicionar servico pre-cadastrado</label>
                        <select class="form-select" wire:model="serviceToAddId">
                            <option value="">Selecione um servico</option>
                            @foreach ($availableServices as $serviceOption)
                                <option value="{{ $serviceOption['id'] }}">{{ $serviceOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" wire:click="addServiceToOrder">Incluir servico na ordem</button>
                    </div>
                </div>

                @forelse ($this->selectedServiceRows as $service)
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-12 col-md-7">
                            <div class="fw-semibold">{{ $service['name'] }}</div>
                            <small class="text-muted">Valor base: R$ {{ number_format($service['base_price'], 2, ',', '.') }}</small>
                        </div>
                        <div class="col-12 col-md-3">
                            <input class="form-control" type="number" min="0" wire:model="selectedServices.{{ $service['id'] }}" placeholder="Qtd (0 = nao incluir)">
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" wire:click="removeServiceFromOrder('{{ $service['id'] }}')">Remover</button>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Nenhum servico incluido na ordem. Adicione um servico pre-cadastrado ou cadastre um novo.</p>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" wire:click="save">Salvar nova ordem</button>
        </div>
    @endif

    @if ($showServiceModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.35);">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar servico</h5>
                        <button type="button" class="btn-close" wire:click="closeServiceModal"></button>
                    </div>
                    <div class="modal-body">
                        <livewire:service-order-service-management :embedded="true" :key="'service-catalog-management-modal'" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeServiceModal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showCustomerModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.35);">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cadastrar cliente (modulo Customer)</h5>
                        <button type="button" class="btn-close" wire:click="closeCustomerModal"></button>
                    </div>
                    <div class="modal-body">
                        <livewire:customer-management :embedded="true" :key="'customer-management-modal'" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeCustomerModal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
