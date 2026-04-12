<div x-on:service-order-create-wizard-open.window="$wire.openWizard()">
    @if ($showCreateWizardModal)
        <div class="modal fade show d-block" tabindex="-1" style="z-index: 3000;" wire:key="so-create-wizard-modal">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('service-order::messages.service_order_wizard_title') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeCreateWizard" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('service-order::messages.equipment_type') }}</label>
                            <select class="form-select" wire:model="createWizard.equipment_type_id">
                                <option value="">{{ trans('service-order::messages.select') }}</option>
                                @foreach ($equipmentTypes as $equipmentType)
                                    <option value="{{ data_get($equipmentType, 'id') }}">{{ data_get($equipmentType, 'name') }}</option>
                                @endforeach
                            </select>
                            @error('createWizard.equipment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        @if (filled(data_get($selectedWizardCustomer, 'name')))
                            <div class="alert alert-primary py-2">
                                <div class="fw-semibold">{{ data_get($selectedWizardCustomer, 'name') }}</div>
                                <small>CPF/CNPJ: {{ data_get($selectedWizardCustomer, 'document_masked', '-') }}</small>
                            </div>
                        @endif

                        @error('createWizard.customer_id') <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary w-100" wire:click="openCreateWizardCustomerSelectModal">
                                {{ trans('service-order::messages.select_customer') }}
                            </button>
                            <button type="button" class="btn btn-primary w-100" wire:click="openCreateWizardCustomerCreateModal">
                                {{ trans('service-order::messages.create_customer') }}
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeCreateWizard">{{ trans('service-order::messages.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmCreateWizard">{{ trans('service-order::messages.continue') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 2990;" wire:key="so-create-wizard-backdrop"></div>
    @endif

    @if ($showCreateWizardModal && $showCreateWizardCustomerSelectModal)
        <div class="modal fade show d-block" tabindex="-1" style="z-index: 3100;" wire:key="so-create-wizard-select-modal">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('service-order::messages.select_customer') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeCreateWizardCustomerSelectModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ trans('service-order::messages.customer_search_to_select') }}</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input type="text" class="form-control" wire:model.live.debounce.300ms="createWizardCustomerSearch" placeholder="{{ trans('service-order::messages.customer_search_placeholder') }}">
                            </div>
                            @error('createWizardCustomerSelectedId') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                        </div>

                        @if (filled(trim($createWizardCustomerSearch)) && collect($wizardCustomerCandidates)->isEmpty())
                            <div class="alert alert-warning py-2 mb-0">
                                {{ trans('service-order::messages.customer_not_found_for_search') }}
                            </div>
                        @endif

                        @if (collect($wizardCustomerCandidates)->isNotEmpty())
                            <div class="d-flex flex-column gap-2 service-order-customer-candidates {{ collect($wizardCustomerCandidates)->count() > 3 ? 'service-order-customer-candidates-scroll' : '' }}">
                                @foreach ($wizardCustomerCandidates as $customerCandidate)
                                    <button
                                        type="button"
                                        class="w-100 p-0 border-0 bg-transparent text-start"
                                        wire:click="selectCreateWizardCustomerCandidate('{{ $customerCandidate['id'] }}')"
                                    >
                                        <div class="card mb-0 {{ $createWizardCustomerSelectedId === $customerCandidate['id'] ? 'border border-primary' : 'border' }}">
                                            <div class="card-body py-2">
                                                <div class="fw-semibold">{{ $customerCandidate['name'] }}</div>
                                                <small class="text-body-secondary">{{ $customerCandidate['document_masked'] }}</small>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeCreateWizardCustomerSelectModal">{{ trans('service-order::messages.cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="confirmCreateWizardSelectedCustomer">{{ trans('service-order::messages.confirm_selection') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 3090;" wire:key="so-create-wizard-select-backdrop"></div>
    @endif

    @if ($showCreateWizardModal && $showCreateWizardCustomerCreateModal)
        <div wire:key="so-create-wizard-customer-create-modal">
            @livewire('customer-management', ['createMode' => true, 'modalId' => 'service-order-create-customer-modal', 'emitEvent' => 'customer-created'], key('service-order-create-customer-' . $createWizardCustomerCreateKey))
        </div>
    @endif
</div>
