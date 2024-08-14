<section>
    <div class="col-md">
        <div class="card mb-4">
            <div class="card-header header-elements">
                <span class="me-2">Filtros</span>
                <div class="card-header-elements ms-auto">
                    <a type="button" class="btn btn-primary" href="{{ route('customers-create') }}">
                        <span class="tf-icon ti ti-plus ti-xs me-1"></span>Cadastrar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="switch">
                            <input wire:model.live="activeonly" class="switch-input" type="checkbox" />
                            <span class="switch-toggle-slider">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                            <span class="switch-label">Somente Cadastros Ativos</span>
                        </label>
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label">Limite por página</label>
                        <select wire:model.live="limiteperpage" class="form-select">
                            <option value="10" selected>10</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-8 mb-3">
                        <label class="form-label">Busque por nome, cpf, cnpj e mais</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                            <input type="text" wire:model.live.debounce.500ms="search" class="form-control"
                                placeholder="Buscar..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <table class="datatables-basic table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nome</th>
                            <th>CPF/CNPJ</th>
                            <th>Celular</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($customers->count() > 0)
                            @foreach ($customers as $customer)
                                <tr wire:key='{{ $customer->id }}'>
                                    <td>{{ $customer->id }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->cpf_cnpj }}</td>
                                    <td>{{ $customer->cellphone }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        <span wire:click="confirmChangeStatus({{ $customer->id }})"
                                            class="badge bg-{{ $customer->status === '1' ? 'success' : 'secondary' }}">
                                            {{ $customer->status === '1' ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="javascript:;" class="btn btn-sm btn-icon"><i
                                                class="text-primary ti ti-trash"></i></a>
                                        <a href="{{ route('customers-edit', $customer) }}"
                                            class="btn btn-sm btn-icon item-edit"><i
                                                class="text-primary ti ti-pencil"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">
                                    Nenhum resultado encontrado
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- <script src="{{ asset(mix('js/app/customer.js')) }}"></script> --}}
</section>
