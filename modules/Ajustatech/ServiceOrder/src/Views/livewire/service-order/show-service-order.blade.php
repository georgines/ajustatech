<x-slot name="page_title">{{ $title }}</x-slot>

<div
    x-data="{
        showDocsModal: false,
        docsModalTitle: '',
        docsItems: [],
        openDocs(orderNumber, docsItems) {
            this.docsModalTitle = `Documentos da OS #${orderNumber}`;
            this.docsItems = docsItems;
            this.showDocsModal = true;
        },
        closeDocs() {
            this.showDocsModal = false;
            this.docsModalTitle = '';
            this.docsItems = [];
        },
        confirmDelete(id) {
            const runDelete = () => $wire.deleteServiceOrder(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: @js(trans('service-order::messages.service_order_confirm_delete')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runDelete();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.service_order_confirm_delete')))) {
                runDelete();
            }
        },
        confirmDuplicate(id) {
            const runDuplicate = () => $wire.duplicateServiceOrder(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: @js(trans('service-order::messages.service_order_confirm_duplicate')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runDuplicate();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.service_order_confirm_duplicate')))) {
                runDuplicate();
            }
        }
    }"
>
    <div class="card mb-3">
        <div class="card-header header-elements">
            <span class="me-2">{{ trans('service-order::messages.filters') }}</span>
            <div class="card-header-elements ms-auto">
                <button type="button" class="btn btn-primary" wire:click="openCreateWizard">
                    <span class="tf-icon ti ti-plus ti-xs me-1"></span>{{ trans('service-order::messages.new_service_order') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="soLimit">{{ trans('service-order::messages.records_per_page') }}</label>
                    <select id="soLimit" class="form-select" wire:model.live="limitePerPage">
                        <option value="10">10</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="soStatus">{{ trans('service-order::messages.status') }}</label>
                    <select id="soStatus" class="form-select" wire:model.live="statusFlowId">
                        <option value="">{{ trans('service-order::messages.status_all') }}</option>
                        @foreach ($statusFlows as $statusFlow)
                            <option value="{{ $statusFlow->id }}">{{ $statusFlow->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="openedFrom">{{ trans('service-order::messages.opened_from') }}</label>
                    <input id="openedFrom" type="date" class="form-control" wire:model.live="openedFrom">
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="openedTo">{{ trans('service-order::messages.opened_to') }}</label>
                    <input id="openedTo" type="date" class="form-control" wire:model.live="openedTo">
                </div>

                <div class="col-12">
                    <label class="form-label" for="soSearch">{{ trans('service-order::messages.search') }}</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input
                            id="soSearch"
                            type="text"
                            class="form-control"
                            placeholder="{{ trans('service-order::messages.service_order_search_placeholder') }}"
                            wire:model.live.debounce.400ms="search"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ trans('service-order::messages.service_order_number') }}</th>
                        <th>{{ trans('service-order::messages.business_days_since_opening') }}</th>
                        <th>{{ trans('service-order::messages.customer') }}</th>
                        <th>{{ trans('service-order::messages.status') }}</th>
                        <th>{{ trans('service-order::messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($serviceOrders as $serviceOrder)
                        <tr>
                            <td>#{{ $serviceOrder['order_number'] }}</td>
                            <td>{{ $serviceOrder['business_days'] }}</td>
                            <td>{{ $serviceOrder['customer_name'] }}</td>
                            <td>
                                <span class="badge bg-label-info">{{ $serviceOrder['status_name'] }}</span>
                            </td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    title="{{ trans('service-order::messages.service_order_documents') }}"
                                    aria-label="{{ trans('service-order::messages.service_order_documents') }}"
                                    x-on:click='openDocs("{{ $serviceOrder['order_number'] }}", @js($serviceOrder['documents']))'
                                >
                                    <i class="text-primary ti ti-help-circle"></i>
                                </button>

                                <a
                                    class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-edit', ['serviceOrder' => $serviceOrder['id']]) }}"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}"
                                >
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDuplicate('{{ $serviceOrder['id'] }}')"
                                    title="{{ trans('service-order::messages.duplicate') }}"
                                    aria-label="{{ trans('service-order::messages.duplicate') }}"
                                >
                                    <i class="text-primary ti ti-copy"></i>
                                </button>

                                <a
                                    class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-list', ['serviceOrder' => $serviceOrder['id']]) }}"
                                    title="{{ trans('service-order::messages.list') }}"
                                    aria-label="{{ trans('service-order::messages.list') }}"
                                >
                                    <i class="text-primary ti ti-list-details"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDelete('{{ $serviceOrder['id'] }}')"
                                    title="{{ trans('service-order::messages.delete') }}"
                                    aria-label="{{ trans('service-order::messages.delete') }}"
                                >
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ trans('service-order::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $serviceOrders->links() }}
        </div>
    </div>

    <div class="modal fade" :class="showDocsModal ? 'show d-block' : ''" tabindex="-1" x-cloak>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="docsModalTitle"></h5>
                    <button type="button" class="btn-close" x-on:click="closeDocs()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="docsItems.length === 0">
                        <p class="mb-0 text-muted">{{ trans('service-order::messages.no_records') }}</p>
                    </template>

                    <template x-for="doc in docsItems" :key="doc.id">
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <h6 class="mb-1" x-text="doc.title"></h6>
                                        <span class="badge bg-label-secondary" x-text="doc.type"></span>
                                    </div>
                                    <a
                                        x-show="doc.path"
                                        :href="doc.path"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        {{ trans('service-order::messages.view') }}
                                    </a>
                                </div>
                                <template x-if="doc.template_preview">
                                    <div class="mt-2 p-2 bg-lighter rounded">
                                        <small class="text-muted" x-text="doc.template_preview"></small>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-backdrop fade" :class="showDocsModal ? 'show' : ''" x-show="showDocsModal" x-cloak></div>

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
                                    <option value="{{ $equipmentType->id }}">{{ $equipmentType->name }}</option>
                                @endforeach
                            </select>
                            @error('createWizard.equipment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        @if ($selectedWizardCustomer)
                            <div class="alert alert-primary py-2">
                                <div class="fw-semibold">{{ $selectedWizardCustomer->name }}</div>
                                <small>CPF/CNPJ: {{ $selectedWizardCustomerDocumentMasked }}</small>
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

                        @if (filled(trim($createWizardCustomerSearch)) && $wizardCustomerCandidates->isEmpty())
                            <div class="alert alert-warning py-2 mb-0">
                                {{ trans('service-order::messages.customer_not_found_for_search') }}
                            </div>
                        @endif

                        @if ($wizardCustomerCandidates->isNotEmpty())
                            <div class="d-flex flex-column gap-2 service-order-customer-candidates {{ $wizardCustomerCandidates->count() > 3 ? 'service-order-customer-candidates-scroll' : '' }}">
                                @foreach ($wizardCustomerCandidates as $customerCandidate)
                                    <button
                                        type="button"
                                        class="w-100 p-0 border-0 bg-transparent text-start"
                                        wire:click="selectCreateWizardCustomerCandidate('{{ $customerCandidate['id'] }}')"
                                    >
                                        <div class="card shadow-none mb-0 {{ $createWizardCustomerSelectedId === $customerCandidate['id'] ? 'bg-label-primary' : 'bg-label-secondary' }}">
                                            <div class="card-body py-2">
                                                <h6 class="card-title mb-1 {{ $createWizardCustomerSelectedId === $customerCandidate['id'] ? 'text-primary' : 'text-secondary' }}">{{ $customerCandidate['name'] }}</h6>
                                                <p class="card-text mb-0 {{ $createWizardCustomerSelectedId === $customerCandidate['id'] ? 'text-primary' : 'text-secondary' }}">CPF/CNPJ: {{ $customerCandidate['document_masked'] }}</p>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" wire:click="closeCreateWizardCustomerSelectModal">{{ trans('service-order::messages.cancel') }}</button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            wire:click="confirmCreateWizardSelectedCustomer"
                            @disabled(blank($createWizardCustomerSelectedId))
                        >
                            {{ trans('service-order::messages.confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 3090;" wire:key="so-create-wizard-select-backdrop"></div>
    @endif

    @if ($showCreateWizardModal && $showCreateWizardCustomerCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="z-index: 3100;" wire:key="so-create-wizard-create-modal">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('service-order::messages.create_customer') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeCreateWizardCustomerCreateModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @livewire('customer-management', ['customer' => new \Ajustatech\Customer\Database\Models\Customer(), 'embedded' => true], key('wizard-customer-create-'.$createWizardCustomerCreateKey))
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 3090;" wire:key="so-create-wizard-create-backdrop"></div>
    @endif
</div>
