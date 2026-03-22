<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="btn-group" role="group" aria-label="Etapas da OS">
                    <button type="button" class="btn btn-sm {{ $currentStep === 'customer' ? 'btn-success' : 'btn-outline-success' }}" disabled>
                        <i class="ti ti-users me-1"></i> CLIENTE
                    </button>
                    <button type="button" class="btn btn-sm {{ $currentStep !== 'customer' ? 'btn-success' : 'btn-outline-success' }}" disabled>
                        <i class="ti ti-device-laptop me-1"></i> EQUIPAMENTO(S)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" disabled>
                        <i class="ti ti-tools me-1"></i> SERVICOS / PRODUTOS
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" disabled>
                        <i class="ti ti-cash me-1"></i> PAGAMENTOS
                    </button>
                </div>
                <span class="badge {{ $customer_id ? 'bg-label-success' : 'bg-label-warning' }}">
                    {{ $customer_id ? 'Cliente selecionado' : 'Selecione um cliente para iniciar' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label text-danger">Numero da O.S.</label>
                            <div class="h3 mb-1">{{ $orderNumberPreview }}</div>
                            <div class="border-top"></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Data de abertura da O.S.</label>
                            <div class="h3 mb-1">{{ $openingDate }}</div>
                            <div class="border-top"></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Hora de abertura da O.S.</label>
                            <div class="h3 mb-1">{{ $openingTime }}</div>
                            <div class="border-top"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <div class="d-flex gap-3 mb-2 small text-muted">
                        <span class="text-success fw-semibold border-bottom border-success">Nome</span>
                        <span>Celular</span>
                        <span>Email</span>
                        <span>CPF / CNPJ</span>
                    </div>

                    <label class="form-label">Busca por Nome...</label>
                    <input type="text"
                           class="form-control"
                           wire:model.live.debounce.300ms="customerSearch"
                           placeholder="Digite pelo menos 2 letras do nome">

                    @if (!empty($customerResults))
                        <div class="list-group mt-2" style="max-height: 220px; overflow-y: auto;">
                            @foreach ($customerResults as $item)
                                <button type="button"
                                        class="list-group-item list-group-item-action"
                                        wire:click="selectCustomer('{{ $item['id'] }}')">
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    <small class="text-muted">
                                        {{ $item['cellphone'] ?: '-' }} | {{ $item['email'] ?: '-' }} | {{ $item['cpf_cnpj'] ?: '-' }}
                                    </small>
                                </button>
                            @endforeach
                        </div>
                    @elseif (mb_strlen(trim($customerSearch)) >= 2)
                        <small class="text-muted d-block mt-2">Cliente nao encontrado. Clique em "Novo Cliente" para cadastrar.</small>
                    @endif
                </div>

                <div class="col-12">
                    @error('customer')
                        <div class="alert alert-danger mb-3">{{ $message }}</div>
                    @enderror

                    @if ($this->selectedCustomer)
                        <div class="alert alert-success mb-3">
                            Cliente selecionado: <strong>{{ $this->selectedCustomer['name'] }}</strong>
                            @if ($this->selectedCustomer['cpf_cnpj'])
                                | {{ $this->selectedCustomer['cpf_cnpj'] }}
                            @endif
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" wire:click="clearSelectedCustomer">Limpar selecao</button>
                        <button type="button" class="btn btn-success" wire:click="openCustomerModal">
                            <i class="ti ti-plus me-1"></i> NOVO CLIENTE
                        </button>
                        <button type="button"
                                class="btn btn-primary"
                                wire:click="proceedToOrder"
                                @disabled(!$this->canCreateOrder)>
                            <i class="ti ti-file-invoice me-1"></i> NOVA ORDEM DE SERVICO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($currentStep === 'order')
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Existem erros que impedem salvar a ordem:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header"><h5 class="m-0">Etapa 2: Equipamento e entrada</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tipo de equipamento</label>
                        <select class="form-select" wire:model.live="equipment_type_id">
                            <option value="">Selecione</option>
                            @foreach ($availableEquipmentTypes as $type)
                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                            @endforeach
                        </select>
                        @error('equipment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Marca <span class="text-danger">*</span></label>
                        <input class="form-control" wire:model.defer="brand" type="text" placeholder="Cadastrar na hora ou selecionar de cadastro futuro">
                        @error('brand') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Modelo <span class="text-danger">*</span></label>
                        <input class="form-control" wire:model.defer="model" type="text" placeholder="Cadastrar na hora ou selecionar de cadastro futuro">
                        @error('model') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Numero de serie</label>
                        <input class="form-control" wire:model.defer="serial_number" type="text">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Relato do cliente</label>
                        <textarea class="form-control" rows="4" wire:model.defer="reported_issue"></textarea>
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
                            <textarea class="form-control" rows="{{ $type === 'document' ? 4 : 2 }}" wire:model.defer="fieldValues.{{ $slug }}"></textarea>
                            @error('fieldValues.' . $slug) <small class="text-danger">{{ $message }}</small> @enderror
                        @elseif ($type === 'select')
                            <select class="form-select" wire:model.defer="fieldValues.{{ $slug }}">
                                <option value="">Selecione</option>
                                @foreach (($field['options'] ?? []) as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            @error('fieldValues.' . $slug) <small class="text-danger">{{ $message }}</small> @enderror
                        @elseif ($type === 'radio')
                            <div>
                                @foreach (($field['options'] ?? []) as $option)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" wire:model.defer="fieldValues.{{ $slug }}" value="{{ $option['value'] }}" id="{{ $slug }}_{{ $option['value'] }}">
                                        <label class="form-check-label" for="{{ $slug }}_{{ $option['value'] }}">{{ $option['label'] }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('fieldValues.' . $slug) <small class="text-danger">{{ $message }}</small> @enderror
                        @elseif ($type === 'photo')
                            <input class="form-control" type="file" wire:model="uploadedFiles.{{ $slug }}" multiple accept="image/*">
                            @error('uploadedFiles.' . $slug) <small class="text-danger">{{ $message }}</small> @enderror
                        @elseif ($type === 'file')
                            <input class="form-control" type="file" wire:model="uploadedFiles.{{ $slug }}" multiple>
                            @error('uploadedFiles.' . $slug) <small class="text-danger">{{ $message }}</small> @enderror
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Selecione um tipo de equipamento para carregar os campos dinamicos.</p>
                @endforelse
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Produtos e servicos deste Equipamento</h4>
                    <button type="button" class="btn btn-primary" wire:click="openServiceModal">
                        <i class="ti ti-tool me-1"></i> NOVO PRODUTO / SERVICO
                    </button>
                </div>

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-12 col-md-7">
                        <label class="form-label">Produto / Servico cadastrado</label>
                        <select class="form-select" wire:model="serviceToAddId">
                            <option value="">Selecione um servico cadastrado</option>
                            @foreach ($availableServices as $serviceOption)
                                <option value="{{ $serviceOption['id'] }}">{{ $serviceOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100" wire:click="addServiceToOrder">ADICIONAR</button>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" wire:click="includeAllRegisteredServices">TODOS</button>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3 bg-label-success">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Produtos</h5>
                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                            <i class="ti ti-arrows-shuffle me-1"></i> MOVIMENTAR ESTOQUES
                        </button>
                    </div>
                    <div class="mb-3">
                        <input class="form-control" type="text" wire:model.live.debounce.300ms="productFilter" placeholder="Filtrar produtos">
                    </div>
                    <div class="row text-muted small fw-semibold mb-2">
                        <div class="col-12 col-md-3">Descricao</div>
                        <div class="col-6 col-md-1">Est.</div>
                        <div class="col-6 col-md-1">Qtd.</div>
                        <div class="col-6 col-md-2">R$ Unit.</div>
                        <div class="col-6 col-md-2">R$ Desc.</div>
                        <div class="col-12 col-md-3">R$ Total</div>
                    </div>
                    <div class="row text-center">
                        <div class="col-12 col-md-4">
                            <div class="small">Subtotal Bruto de Produtos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->productSummary['gross'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="small">Descontos de Produtos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->productSummary['discount'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="small">Subtotal Liquido de Produtos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->productSummary['net'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="border rounded p-3 mb-3 bg-label-info">
                    <h5 class="mb-2">Servicos</h5>
                    <div class="mb-3">
                        <input class="form-control" type="text" wire:model.live.debounce.300ms="serviceFilter" placeholder="Filtrar servicos">
                    </div>
                    <div class="row text-muted small fw-semibold mb-2">
                        <div class="col-12 col-md-5">Servico</div>
                        <div class="col-6 col-md-1">Qtd.</div>
                        <div class="col-6 col-md-2">R$ Unit.</div>
                        <div class="col-6 col-md-2">R$ Desc.</div>
                        <div class="col-6 col-md-2">R$ Total</div>
                    </div>
                    @forelse ($this->filteredServiceRows as $service)
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-12 col-md-5">
                                <div class="fw-semibold">{{ $service['name'] }}</div>
                            </div>
                            <div class="col-6 col-md-1">
                                <span class="fw-semibold">{{ $service['quantity'] }}</span>
                            </div>
                            <div class="col-6 col-md-2">R$ {{ number_format($service['base_price'], 2, ',', '.') }}</div>
                            <div class="col-6 col-md-2">R$ {{ number_format($service['discount'], 2, ',', '.') }}</div>
                            <div class="col-6 col-md-1">R$ {{ number_format($service['line_total'], 2, ',', '.') }}</div>
                            <div class="col-12 col-md-1 d-flex justify-content-end align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" wire:click="openServiceEditModal('{{ $service['id'] }}')" title="Editar servico">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger" wire:click="confirmRemoveService('{{ $service['id'] }}')" title="Remover servico">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-2">Nenhum servico adicionado.</p>
                    @endforelse
                    <div class="row text-center">
                        <div class="col-12 col-md-4">
                            <div class="small">Subtotal Bruto de Servicos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->serviceSummary['gross'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="small">Descontos de Servicos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->serviceSummary['discount'], 2, ',', '.') }}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="small">Subtotal Liquido de Servicos:</div>
                            <div class="fw-bold">R$ {{ number_format($this->serviceSummary['net'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="border rounded p-3 text-white" style="background-color: #3f5c00;">
                    <h5 class="mb-2 text-white">Subtotais deste Equipamento</h5>
                    <div class="row text-center">
                        <div class="col-12 col-md-4">
                            <div>Subtotal Bruto: <strong>R$ {{ number_format($this->equipmentTotals['gross'], 2, ',', '.') }}</strong></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div>Descontos: <strong>R$ {{ number_format($this->equipmentTotals['discount'], 2, ',', '.') }}</strong></div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div>Subtotal Liquido: <strong>R$ {{ number_format($this->equipmentTotals['net'], 2, ',', '.') }}</strong></div>
                        </div>
                    </div>
                </div>
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

    @if ($showServiceEditModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,.35);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar servico da ordem</h5>
                        <button type="button" class="btn-close" wire:click="closeServiceEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Servico</label>
                            <select class="form-select" wire:model.live="editServiceSelectionId">
                                <option value="">Selecione</option>
                                @foreach ($availableServices as $serviceOption)
                                    <option value="{{ $serviceOption['id'] }}">{{ $serviceOption['name'] }}</option>
                                @endforeach
                            </select>
                            @error('editServiceSelectionId') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valor unitario</label>
                            <input class="form-control" type="text" value="R$ {{ number_format($this->editServiceUnitPrice, 2, ',', '.') }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quantidade</label>
                            <input class="form-control" type="number" min="1" max="100" wire:model.live="editServiceQty">
                            @error('editServiceQty') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Desconto (R$)</label>
                            <input class="form-control" type="text" wire:model.live="editServiceDiscount" placeholder="Ex.: 10,50">
                            @error('editServiceDiscount') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Total</label>
                            <input class="form-control" type="text" value="R$ {{ number_format($this->editServiceNetAmount, 2, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeServiceEditModal">Cancelar</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmSaveServiceEdition">Salvar alteracao</button>
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
                        <h5 class="modal-title">Cadastrar cliente</h5>
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
