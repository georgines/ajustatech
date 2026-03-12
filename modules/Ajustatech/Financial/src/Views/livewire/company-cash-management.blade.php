<x-slot name='page_title'>
    {{ $title }}
</x-slot>

<section>
    <div class="row">
        <div class="col">
            <div class="card mb-3">
                <div class="card-header pt-2">
                    <h5>{{ $title }}</h5>
                </div>
                <form wire:submit.prevent="save">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3 validation-error">
                                <label class="form-label" for="name">Nome do Caixa</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-name" class="input-group-text @error('name') is-invalid-field @enderror">
                                        <i class="ti ti-briefcase"></i>
                                    </span>
                                    <input wire:model="name" type="text" class="form-control @error('name') is-invalid-field @enderror" id="name" />
                                </div>
                                @error('name')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 mb-3 validation-error">
                                <label class="form-label" for="initialBalance">Saldo Inicial</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-balance" class="input-group-text @error('initialBalance') is-invalid-field @enderror">
                                        <i class="ti ti-currency-dollar"></i>
                                    </span>
                                    <input wire:model="initialBalance" type="text" class="form-control @error('initialBalance') is-invalid-field @enderror" id="initialBalance" />
                                </div>
                                @error('initialBalance')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input wire:model="isOnline" class="form-check-input" type="checkbox" id="isOnline">
                            <label class="form-check-label" for="isOnline">Caixa Online</label>
                        </div>

                        @if($isOnline)
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="agency">Agência</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-agency" class="input-group-text @error('agency') is-invalid-field @enderror">
                                            <i class="ti ti-bank"></i>
                                        </span>
                                        <input wire:model="agency" type="text" class="form-control @error('agency') is-invalid-field @enderror" id="agency" />
                                    </div>
                                    @error('agency')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6 mb-3 validation-error">
                                    <label class="form-label" for="account">Conta</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-account" class="input-group-text @error('account') is-invalid-field @enderror">
                                            <i class="ti ti-credit-card"></i>
                                        </span>
                                        <input wire:model="account" type="text" class="form-control @error('account') is-invalid-field @enderror" id="account" />
                                    </div>
                                    @error('account')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12 mb-3 validation-error">
                                <label class="form-label" for="description">Descrição</label>
                                <div class="input-group input-group-merge">
                                    <span id="basic-icon-default-description" class="input-group-text @error('description') is-invalid-field @enderror">
                                        <i class="ti ti-align-left"></i>
                                    </span>
                                    <textarea wire:model="description" id="description" class="form-control @error('description') is-invalid-field @enderror"></textarea>
                                </div>
                                @error('description')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Salvar</button>
                        {{-- <a href="{{ route('company-cash-list') }}" class="btn btn-label-secondary">Cancelar</a> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
