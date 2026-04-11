<x-slot name="page_title">{{ $title }}</x-slot>

<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ trans('service-order::messages.service_order_form_title') }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-none bg-label-secondary h-100 mb-0">
                            <div class="card-body py-3">
                                <small class="text-body-secondary d-block mb-1">{{ trans('service-order::messages.service_order_number') }}</small>
                                <h6 class="mb-0 text-secondary">{{ $currentServiceOrder?->order_number ? '#'.$currentServiceOrder->order_number : trans('service-order::messages.automatic_after_create') }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-none bg-label-secondary h-100 mb-0">
                            <div class="card-body py-3">
                                <small class="text-body-secondary d-block mb-1">{{ trans('service-order::messages.opened_at') }}</small>
                                <h6 class="mb-0 text-secondary">{{ $currentServiceOrder?->opened_at?->format('d/m/Y H:i') ?? '-' }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-none bg-label-secondary h-100 mb-0">
                            <div class="card-body py-3">
                                <small class="text-body-secondary d-block mb-1">{{ trans('service-order::messages.finished_at') }}</small>
                                <h6 class="mb-0 text-secondary">{{ $currentServiceOrder?->finished_at?->format('d/m/Y H:i') ?? '-' }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-none bg-label-primary h-100 mb-0">
                            <div class="card-body py-3">
                                <small class="text-body-secondary d-block mb-1">{{ trans('service-order::messages.status') }}</small>
                                <h6 class="mb-0 text-primary">
                                    @if (($currentServiceOrder?->statusFlow?->code ?? null) === 'entrada')
                                        {{ trans('service-order::messages.open_status') }}
                                    @else
                                        {{ $currentServiceOrder?->statusFlow?->name ?? trans('service-order::messages.status_flow_auto') }}
                                    @endif
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">{{ trans('service-order::messages.customer') }}</h6>
                <div class="d-flex gap-2">
                    @if ($serviceOrderForm['customer_id'])
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            wire:click="prepareCustomerCorrectionModal"
                            data-bs-toggle="modal"
                            data-bs-target="#customerCorrectionModal"
                        >
                            {{ trans('service-order::messages.update_customer_data') }}
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if ($selectedCustomer)
                    <div class="card shadow-none bg-label-secondary mb-0">
                        <div class="card-body py-3">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex flex-column flex-sm-row justify-content-between gap-1">
                                    <span class="fw-medium text-heading">{{ trans('service-order::messages.customer_name') }}</span>
                                    <span class="text-break text-sm-end">{{ $selectedCustomer->name }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex flex-column flex-sm-row justify-content-between gap-1">
                                    <span class="fw-medium text-heading">{{ trans('service-order::messages.customer_document') }}</span>
                                    <span class="text-break text-sm-end">{{ $selectedCustomer->cpf_cnpj }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex flex-column flex-sm-row justify-content-between gap-1">
                                    <span class="fw-medium text-heading">{{ trans('service-order::messages.customer_phone') }}</span>
                                    <span class="text-break text-sm-end">{{ $selectedCustomer->cellphone ?: '-' }}</span>
                                </li>
                                <li class="list-group-item px-0 pb-0 d-flex flex-column flex-sm-row justify-content-between gap-1">
                                    <span class="fw-medium text-heading">{{ trans('service-order::messages.customer_email') }}</span>
                                    <span class="text-break text-sm-end">{{ $selectedCustomer->email ?: '-' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">{{ trans('service-order::messages.customer_required_to_create_order') }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.equipment_data') }}</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_type') }}</label>
                        <select class="form-select" wire:model.live="serviceOrderForm.equipment_type_id" @disabled($mode === 'view')>
                            <option value="">{{ trans('service-order::messages.select') }}</option>
                            @foreach ($equipmentTypes as $equipmentType)
                                <option value="{{ $equipmentType->id }}">{{ $equipmentType->name }}</option>
                            @endforeach
                        </select>
                        @error('serviceOrderForm.equipment_type_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_brand') }}</label>
                        <input type="text" class="form-control" wire:model="serviceOrderForm.equipment_brand" @disabled($mode === 'view')>
                        @error('serviceOrderForm.equipment_brand') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_model') }}</label>
                        <input type="text" class="form-control" wire:model="serviceOrderForm.equipment_model" @disabled($mode === 'view')>
                        @error('serviceOrderForm.equipment_model') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ trans('service-order::messages.equipment_serial_number') }}</label>
                        <input type="text" class="form-control" wire:model="serviceOrderForm.equipment_serial_number" @disabled($mode === 'view')>
                        @error('serviceOrderForm.equipment_serial_number') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ trans('service-order::messages.print_document') }}</label>
                        <select class="form-select" wire:model="serviceOrderForm.selected_document_id" @disabled($mode === 'view')>
                            <option value="">{{ trans('service-order::messages.select') }}</option>
                            @foreach ($equipmentDocuments as $document)
                                <option value="{{ $document->id }}">{{ $document->title }} ({{ $document->document_type }})</option>
                            @endforeach
                        </select>
                        @error('serviceOrderForm.selected_document_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.custom_fields') }}</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse ($dynamicFields as $index => $dynamicField)
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ $dynamicField['field_label'] }}</label>
                            <input type="text" class="form-control" wire:model="dynamicFields.{{ $index }}.value_text" placeholder="{{ $dynamicField['field_placeholder'] }}" @disabled($mode === 'view')>
                            @error('dynamicFields.'.$index.'.value_text') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">{{ trans('service-order::messages.no_custom_fields_for_selected_equipment') }}</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">{{ trans('service-order::messages.services') }}</h6>
                @if ($mode !== 'view')
                    <button type="button" class="btn btn-sm btn-primary" wire:click="addServiceItem">{{ trans('service-order::messages.add_service_item') }}</button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('service-order::messages.item') }}</th>
                                <th>{{ trans('service-order::messages.value') }}</th>
                                <th>{{ trans('service-order::messages.discount') }}</th>
                                <th>{{ trans('service-order::messages.total') }}</th>
                                <th>{{ trans('service-order::messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($serviceItems as $index => $serviceItem)
                                <tr>
                                    <td>
                                        <select class="form-select mb-2" wire:change="applyProcedureToItem({{ $index }}, $event.target.value)" @disabled($mode === 'view')>
                                            <option value="">{{ trans('service-order::messages.select_service') }}</option>
                                            @foreach ($procedures as $procedure)
                                                <option value="{{ $procedure->id }}" @selected(($serviceItem['procedure_id'] ?? '') === $procedure->id)>{{ $procedure->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" class="form-control mb-2" wire:model="serviceItems.{{ $index }}.item_name" placeholder="{{ trans('service-order::messages.item_name') }}" @disabled($mode === 'view')>
                                        <textarea class="form-control" wire:model="serviceItems.{{ $index }}.item_notes" rows="2" placeholder="{{ trans('service-order::messages.notes_optional') }}" @disabled($mode === 'view')></textarea>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control" wire:model.blur="serviceItems.{{ $index }}.unit_value" @disabled($mode === 'view')>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="number" step="0.01" min="0" class="form-control" wire:model.blur="serviceItems.{{ $index }}.discount_value" @disabled($mode === 'view')>
                                            @if ($mode !== 'view')
                                                <button type="button" class="btn btn-sm btn-icon" wire:click="openDiscountModal({{ $index }})" title="{{ trans('service-order::messages.apply_discount') }}" aria-label="{{ trans('service-order::messages.apply_discount') }}">
                                                    <i class="text-primary ti ti-percentage"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        R$ {{ number_format(max(0, (float) ($serviceItem['unit_value'] ?? 0) - (float) ($serviceItem['discount_value'] ?? 0)), 2, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($mode !== 'view')
                                            <button type="button" class="btn btn-sm btn-icon" wire:click="removeServiceItem({{ $index }})" title="{{ trans('service-order::messages.delete') }}" aria-label="{{ trans('service-order::messages.delete') }}">
                                                <i class="text-primary ti ti-trash"></i>
                                            </button>
                                        @endif
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
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.products') }}</h6></div>
            <div class="card-body">
                <div class="alert alert-info mb-0">{{ trans('service-order::messages.products_placeholder') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-end gap-2 flex-wrap">
                @if ($mode !== 'view')
                    <button type="button" class="btn btn-primary" wire:click="save">{{ $mode === 'create' ? trans('service-order::messages.save') : trans('service-order::messages.update') }}</button>
                @endif
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">{{ trans('service-order::messages.print') }}</button>
            </div>
        </div>
    </div>

@if ($showCustomerModal)
    <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.select_or_create_customer') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeCustomerModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $customerModalTab === 'list' ? 'active' : '' }}" wire:click="$set('customerModalTab', 'list')">{{ trans('service-order::messages.list') }}</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ $customerModalTab === 'create' ? 'active' : '' }}" wire:click="$set('customerModalTab', 'create')">{{ trans('service-order::messages.create') }}</button>
                        </li>
                    </ul>

                    @if ($customerModalTab === 'list')
                        <div class="mb-3">
                            <label class="form-label">{{ trans('service-order::messages.search') }}</label>
                            <input type="text" class="form-control" wire:model.live.debounce.300ms="customerSearch" placeholder="{{ trans('service-order::messages.customer_search_placeholder') }}">
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ trans('service-order::messages.customer_name') }}</th>
                                        <th>{{ trans('service-order::messages.customer_document') }}</th>
                                        <th>{{ trans('service-order::messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($availableCustomers as $customer)
                                        <tr>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->cpf_cnpj }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" wire:click="selectCustomer('{{ $customer->id }}')">{{ trans('service-order::messages.select') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.customer_name') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.name">
                                @error('newCustomer.name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.person_type') }}</label>
                                <select class="form-select" wire:model="newCustomer.person">
                                    <option value="F">F</option>
                                    <option value="J">J</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.customer_document') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.cpf_cnpj">
                                @error('newCustomer.cpf_cnpj') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.customer_email') }}</label>
                                <input type="email" class="form-control" wire:model="newCustomer.email">
                                @error('newCustomer.email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.customer_phone') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.cellphone">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">CEP</label>
                                <input type="text" class="form-control" wire:model="newCustomer.zip_code">
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label">{{ trans('service-order::messages.address') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.address">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">{{ trans('service-order::messages.number') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.number">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ trans('service-order::messages.neighborhood') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.neighborhood">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">{{ trans('service-order::messages.city') }}</label>
                                <input type="text" class="form-control" wire:model="newCustomer.city">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">{{ trans('service-order::messages.state') }}</label>
                                <input type="text" maxlength="2" class="form-control" wire:model="newCustomer.state">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeCustomerModal">{{ trans('service-order::messages.cancel') }}</button>
                    @if ($customerModalTab === 'create')
                        <button type="button" class="btn btn-primary" wire:click="createCustomerFromModal">{{ trans('service-order::messages.create') }}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@if ($selectedCustomer)
    <div class="modal fade" id="customerCorrectionModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.correct_customer_data') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($customerForCorrectionModal)
                        @livewire(
                            'customer-management',
                            ['customer' => $customerForCorrectionModal, 'embedded' => true],
                            key('service-order-customer-correction-'.$customerCorrectionComponentKey.'-'.$customerForCorrectionModal->id)
                        )
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@if ($showDiscountModal)
    <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.apply_discount') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeDiscountModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ trans('service-order::messages.discount') }}</label>
                    <input type="number" step="0.01" min="0" class="form-control" wire:model="discountInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeDiscountModal">{{ trans('service-order::messages.cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="applyDiscount">{{ trans('service-order::messages.apply') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

</div>
