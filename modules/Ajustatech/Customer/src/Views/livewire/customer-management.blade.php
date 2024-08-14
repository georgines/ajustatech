<section>
    <div class="row">
        <div class="col">
            <div class="card mb-3">
                <div class="card-header pt-2">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal-info"
                                role="tab" aria-selected="true">
                                Dados do Cliente
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#address-info" role="tab"
                                aria-selected="false">
                                Endereço
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#other-info" role="tab"
                                aria-selected="false">
                                Outros
                            </button>
                        </li>
                    </ul>
                </div>
                <form wire:submit.prevent="save">
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="personal-info" role="tabpanel">
                            <div class="row">
                                <div class="col-12 col-md-4 mb-3">
                                    <label class="form-label" for="person">Tipo de Pessoa</label>
                                    <div class="input-group input-group-merge">
                                        <select wire:model.live="customer_.person" class="form-select" id="person">
                                            <option value="F">Física</option>
                                            <option value="J">Jurídica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8 mb-3 validation-error">
                                    <label class="form-label" for="name">
                                        @if ($customer_['person'] == 'F')
                                            Nome Completo
                                        @else
                                            Razão Social
                                        @endif
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.name')is-invalid-field @enderror">
                                            <i class="ti ti-user"></i>
                                        </span>
                                        <input wire:model="customer_.name" type="text"
                                            class="form-control @error('customer_.name')is-invalid-field @enderror"
                                            id="name" />
                                    </div>
                                    @error('customer_.name')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-12 mb-3 validation-error">
                                    <label class="form-label" for="cpf">
                                        @if ($customer_['person'] == 'F')
                                            CPF
                                        @else
                                            CNPJ
                                        @endif
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.cpf_cnpj')is-invalid-field @enderror">
                                            <i class="ti ti-id"></i>
                                        </span>
                                        <input wire:model="customer_.cpf_cnpj" type="text"
                                            class="form-control  @error('customer_.cpf_cnpj')is-invalid-field @enderror"
                                            id="cpf" />
                                    </div>
                                    @error('customer_.cpf_cnpj')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="cellphone">Celular</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.cellphone')is-invalid-field @enderror">
                                            <i class="ti ti-brand-whatsapp"></i>
                                        </span>
                                        <input wire:model="customer_.cellphone" type="text"
                                            class="form-control  @error('customer_.cellphone')is-invalid-field @enderror"
                                            id="cellphone" />
                                    </div>
                                    @error('customer_.cellphone')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="phone">Telefone</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.phone')is-invalid-field @enderror">
                                            <i class="ti ti-phone"></i>
                                        </span>
                                        <input wire:model="customer_.phone" type="text"
                                            class="form-control  @error('customer_.phone')is-invalid-field @enderror"
                                            id="phone" />
                                    </div>
                                    @error('customer_.phone')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="email">Email</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.email')is-invalid-field @enderror">
                                            <i class="ti ti-mail"></i>
                                        </span>
                                        <input wire:model="customer_.email" type="text"
                                            class="form-control  @error('customer_.email')is-invalid-field @enderror"
                                            id="email" />
                                    </div>
                                    @error('customer_.email')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="dateofbirth">Data de Nascimento</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.date_of_birth')is-invalid-field @enderror">
                                            <i class="ti ti-calendar"></i>
                                        </span>
                                        <input wire:model="customer_.date_of_birth" type="date"
                                            class="form-control  @error('customer_.date_of_birth')is-invalid-field @enderror"
                                            id="dateofbirth" />
                                    </div>
                                    @error('customer_.date_of_birth')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="address-info" role="tabpanel">
                            <div class="row">
                                <div class="col-12 col-md-10 mb-3 validation-error">
                                    <label class="form-label" for="address">Endereço</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.address')is-invalid-field @enderror">
                                            <i class="ti ti-building"></i>
                                        </span>
                                        <input wire:model="customer_.address" type="text"
                                            class="form-control  @error('customer_.address')is-invalid-field @enderror"
                                            id="address" />
                                    </div>
                                    @error('customer_.address')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-2 mb-3 validation-error">
                                    <label class="form-label" for="number">Número</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.number')is-invalid-field @enderror">
                                            <i class="ti ti-home"></i>
                                        </span>
                                        <input wire:model="customer_.number" type="text"
                                            class="form-control  @error('customer_.number')is-invalid-field @enderror"
                                            id="number" />
                                    </div>
                                    @error('customer_.number')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3 validation-error">
                                    <label class="form-label" for="complement">Complemento</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.complement')is-invalid-field @enderror">
                                            <i class="ti ti-home-move"></i>
                                        </span>
                                        <input wire:model="customer_.complement" type="text"
                                            class="form-control  @error('customer_.complement')is-invalid-field @enderror"
                                            id="complement" />
                                    </div>
                                    @error('customer_.complement')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="neighborhood">Bairro</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.neighborhood')is-invalid-field @enderror">
                                            <i class="ti ti-map"></i>
                                        </span>
                                        <input wire:model="customer_.neighborhood" type="text"
                                            class="form-control  @error('customer_.neighborhood')is-invalid-field @enderror"
                                            id="neighborhood" />
                                    </div>
                                    @error('customer_.neighborhood')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="zipcode">CEP</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.zip_code')is-invalid-field @enderror">
                                            <i class="ti ti-map-2"></i>
                                        </span>
                                        <input wire:model="customer_.zip_code" type="text"
                                            class="form-control  @error('customer_.zip_code')is-invalid-field @enderror"
                                            id="zipcode" />
                                    </div>
                                    @error('customer_.zip_code')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="city">Cidade</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2"
                                            class="input-group-text  @error('customer_.city')is-invalid-field @enderror">
                                            <i class="ti ti-map-pin-filled"></i>
                                        </span>
                                        <input wire:model="customer_.city" type="text"
                                            class="form-control  @error('customer_.city')is-invalid-field @enderror"
                                            id="city" />
                                    </div>
                                    @error('customer_.city')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="state">Estado</label>
                                    <div class="input-group input-group-merge">
                                        <select wire:model="customer_.state"
                                            class="form-select  @error('customer_.state')is-invalid-field @enderror"
                                            id="state">
                                            <option value="-1">Selecione o Estado</option>
                                            <option value="AC">Acre</option>
                                            <option value="AL">Alagoas</option>
                                            <option value="AP">Amapá</option>
                                            <option value="AM">Amazonas</option>
                                            <option value="BA">Bahia</option>
                                            <option value="CE">Ceará</option>
                                            <option value="DF">Distrito Federal</option>
                                            <option value="ES">Espírito Santo</option>
                                            <option value="GO">Goiás</option>
                                            <option value="MA">Maranhão</option>
                                            <option value="MT">Mato Grosso</option>
                                            <option value="MS">Mato Grosso do Sul</option>
                                            <option value="MG">Minas Gerais</option>
                                            <option value="PA">Pará</option>
                                            <option value="PB">Paraíba</option>
                                            <option value="PR">Paraná</option>
                                            <option value="PE">Pernambuco</option>
                                            <option value="PI">Piauí</option>
                                            <option value="RJ">Rio de Janeiro</option>
                                            <option value="RN">Rio Grande do Norte</option>
                                            <option value="RS">Rio Grande do Sul</option>
                                            <option value="RO">Rondônia</option>
                                            <option value="RR">Roraima</option>
                                            <option value="SC">Santa Catarina</option>
                                            <option value="SP">São Paulo</option>
                                            <option value="SE">Sergipe</option>
                                            <option value="TO">Tocantins</option>
                                        </select>
                                    </div>
                                    @error('customer_.state')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="other-info" role="tabpanel">
                            <div class="mb-3 validation-error">
                                <label class="form-label" for="observations">Observações</label>
                                <div class="input-group input-group-merge">
                                    <span
                                        class="input-group-text  @error('customer_.observations')is-invalid-field @enderror"><i
                                            class="ti ti-message-dots"></i></span>
                                    <textarea wire:model="customer_.observations" id="observations"
                                        class="form-control  @error('customer_.observations')is-invalid-field @enderror"></textarea>
                                </div>
                                @error('customer_.observations')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Salvar</button>
                        <a href="{{ route('customers-show') }}" class="btn btn-label-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
