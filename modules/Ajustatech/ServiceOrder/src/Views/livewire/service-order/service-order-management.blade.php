<x-slot name="page_title">{{ $title }}</x-slot>

<div
    class="row g-3"
    x-data="{
        documentPreview: {
            title: '',
            documentTypeLabel: '',
            previewUrl: '',
            previewText: '',
            icon: '',
        },
        openDocumentPreview(doc) {
            this.documentPreview = {
                title: doc.title ?? '',
                documentTypeLabel: doc.document_type_label ?? '',
                previewUrl: doc.preview_url ?? '',
                previewText: doc.preview_text ?? '',
                icon: doc.icon ?? '',
            };

            if (!window.bootstrap) {
                return;
            }

            const modalEl = window.document.getElementById('documentPreviewModal');

            if (!modalEl) {
                return;
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        },
    }"
>
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
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <h6 class="mb-0">{{ trans('service-order::messages.equipment_data') }}</h6>
                @if ($mode !== 'view')
                    <button type="button" class="btn btn-sm btn-outline-primary align-self-start align-self-sm-center" wire:click="openEquipmentTypeModal">
                        {{ trans('service-order::messages.equipment_type_edit') }}
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="card shadow-none bg-label-primary mb-3">
                    <div class="card-body py-3">
                        <small class="text-primary d-block mb-1">{{ trans('service-order::messages.equipment_type') }}</small>
                        <div class="fw-semibold text-primary">{{ $selectedEquipmentType?->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_brand') }}</label>
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control border-primary"
                                wire:model="serviceOrderForm.equipment_brand"
                                readonly
                                @disabled($mode === 'view' || ! $selectedEquipmentType)
                                placeholder="{{ trans('service-order::messages.equipment_brand_placeholder') }}"
                                x-on:mousedown.prevent="$wire.openEquipmentBrandModal()"
                                x-on:click.prevent="$wire.openEquipmentBrandModal()"
                            >
                        </div>
                        @error('serviceOrderForm.equipment_brand') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_model') }}</label>
                        <div class="input-group">
                            <input
                                type="text"
                                class="form-control border-primary"
                                wire:model="serviceOrderForm.equipment_model"
                                readonly
                                @disabled($mode === 'view' || ! $selectedEquipmentType)
                                placeholder="{{ trans('service-order::messages.equipment_model_placeholder') }}"
                                x-on:mousedown.prevent="$wire.openEquipmentModelModal()"
                                x-on:click.prevent="$wire.openEquipmentModelModal()"
                            >
                        </div>
                        @error('serviceOrderForm.equipment_model') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">{{ trans('service-order::messages.equipment_serial_number') }}</label>
                        <input type="text" class="form-control border-primary" wire:model="serviceOrderForm.equipment_serial_number" @disabled($mode === 'view')>
                        @error('serviceOrderForm.equipment_serial_number') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ trans('service-order::messages.custom_fields') }}</label>
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
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <h6 class="mb-0">{{ trans('service-order::messages.services') }}</h6>
                @if ($mode !== 'view')
                    <button type="button" class="btn btn-sm btn-outline-primary align-self-start align-self-sm-center" wire:click="addServiceItem">
                        {{ trans('service-order::messages.add_service_item') }}
                    </button>
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
                                <tr wire:key="service-item-{{ $index }}">
                                    <td>
                                        @if (($serviceItem['item_name'] ?? '') === '' && $mode !== 'view')
                                            <select class="form-select" wire:change="applyAnalysisServiceToItem({{ $index }}, $event.target.value)" @disabled($mode === 'view')>
                                                <option value="">{{ trans('service-order::messages.select_service') }}</option>
                                                @foreach ($analysisServices as $analysisService)
                                                    <option value="{{ $analysisService->id }}">{{ $analysisService->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <div class="fw-semibold text-heading">{{ $serviceItem['item_name'] ?: trans('service-order::messages.select_service') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-heading">R$ {{ number_format(max(0, (float) ($serviceItem['unit_value'] ?? 0)), 2, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="text-heading">R$ {{ number_format(max(0, (float) ($serviceItem['discount_value'] ?? 0)), 2, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">R$ {{ number_format(max(0, (float) ($serviceItem['unit_value'] ?? 0) - (float) ($serviceItem['discount_value'] ?? 0)), 2, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            @if ($mode !== 'view')
                                                <button type="button" class="btn btn-sm btn-icon" wire:click="openDiscountModal({{ $index }})" title="{{ trans('service-order::messages.edit') }}" aria-label="{{ trans('service-order::messages.edit') }}">
                                                    <i class="text-primary ti ti-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-icon" wire:click="removeServiceItem({{ $index }})" title="{{ trans('service-order::messages.delete') }}" aria-label="{{ trans('service-order::messages.delete') }}">
                                                    <i class="text-primary ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ trans('service-order::messages.no_records') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end fw-semibold fs-5 text-body-secondary">{{ trans('service-order::messages.subtotal') }}</th>
                                <th class="text-primary fw-semibold fs-5 text-nowrap">R$ {{ $serviceItemsSubtotal }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
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
            <div class="card-header">
                <h6 class="mb-0">{{ trans('service-order::messages.print_document') }}</h6>
            </div>
            <div class="card-body">
                @if ($equipmentDocuments)
                    <div class="row g-3 row-cols-1 row-cols-md-2 row-cols-xl-3">
                        @foreach ($equipmentDocuments as $document)
                            <div class="col">
                                <label class="form-check custom-option custom-option-icon h-100 mb-0 {{ $document['is_selected'] ? 'checked' : '' }}">
                                    <span class="form-check-label custom-option-content h-100 d-flex flex-column">
                                        <span class="custom-option-body d-flex flex-column flex-grow-1 gap-3">
                                            <span class="d-flex justify-content-between align-items-start gap-3">
                                                <i class="icon-base ti {{ $document['icon'] }} text-primary fs-3"></i>
                                                <input
                                                    class="form-check-input"
                                                    type="radio"
                                                    name="selectedDocument"
                                                    value="{{ $document['id'] }}"
                                                    wire:model.live="serviceOrderForm.selected_document_id"
                                                    @disabled($mode === 'view')
                                                    @checked($document['is_selected'])
                                                >
                                            </span>
                                            <span class="custom-option-title mb-0">{{ $document['title'] }}</span>
                                            <small class="text-body-secondary">{{ $document['document_type_label'] }}</small>
                                        </span>
                                        <span class="mt-3 d-flex justify-content-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-icon"
                                                title="{{ trans('service-order::messages.equipment_type_view_document') }}"
                                                aria-label="{{ trans('service-order::messages.equipment_type_view_document') }}"
                                                x-on:click.stop.prevent="openDocumentPreview(@js($document))"
                                            >
                                                <i class="text-primary ti ti-eye"></i>
                                            </button>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">{{ trans('service-order::messages.equipment_type_empty_section') }}</div>
                @endif
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

@if ($showEquipmentBrandModal)
    <div class="modal fade show d-block" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.equipment_brand_modal_title') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeEquipmentBrandModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($equipmentBrandSearchActive && $equipmentTypeBrands->isEmpty())
                        <p class="text-body-secondary small mb-3">
                            {{ trans('service-order::messages.equipment_brand_no_results') }}
                        </p>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="equipmentBrandSearch">{{ trans('service-order::messages.equipment_brand') }}</label>
                        <input id="equipmentBrandSearch" type="text" class="form-control @error('equipmentBrandSearch') is-invalid @enderror" wire:model.live.debounce.300ms="equipmentBrandSearch" placeholder="{{ trans('service-order::messages.equipment_brand_search_placeholder') }}">
                        @error('equipmentBrandSearch') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    </div>

                    <div class="list-group">
                        @if ($equipmentBrandSearchActive && $equipmentTypeBrands->isNotEmpty())
                            @foreach ($equipmentTypeBrands as $brand)
                                <button type="button" class="list-group-item list-group-item-action" wire:click="selectEquipmentBrand('{{ $brand->id }}')">
                                    {{ $brand->name }}
                                </button>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="saveEquipmentBrand"
                        @disabled(blank($equipmentBrandSearch) || $equipmentTypeBrands->isNotEmpty())
                    >
                        {{ trans('service-order::messages.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@if ($showEquipmentModelModal)
    <div class="modal fade show d-block" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.equipment_model_modal_title') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeEquipmentModelModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedEquipmentBrandId && $equipmentModelSearchActive && $equipmentTypeModels->isEmpty())
                        <p class="text-body-secondary small mb-3">
                            {{ trans('service-order::messages.equipment_model_no_results') }}
                        </p>
                    @endif

                    <div class="mb-3">
                        <label class="form-label" for="equipmentModelSearch">{{ trans('service-order::messages.equipment_model') }}</label>
                        <input id="equipmentModelSearch" type="text" class="form-control @error('equipmentModelSearch') is-invalid @enderror" wire:model.live.debounce.300ms="equipmentModelSearch" placeholder="{{ trans('service-order::messages.equipment_model_search_placeholder') }}">
                        @error('equipmentModelSearch') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    </div>

                    <div class="list-group">
                        @if ($selectedEquipmentBrandId && $equipmentModelSearchActive && $equipmentTypeModels->isNotEmpty())
                            @foreach ($equipmentTypeModels as $equipmentModel)
                                <button type="button" class="list-group-item list-group-item-action" wire:click="selectEquipmentModel('{{ $equipmentModel->id }}')">
                                    {{ $equipmentModel->name }}
                                </button>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="saveEquipmentModel"
                        @disabled(blank($equipmentModelSearch) || blank($selectedEquipmentBrandId) || $equipmentTypeModels->isNotEmpty())
                    >
                        {{ trans('service-order::messages.save') }}
                    </button>
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
                    @livewire(
                        'customer-management',
                        ['customer' => $customerForCorrectionModal, 'embedded' => true],
                        key('service-order-customer-correction-'.$customerCorrectionComponentKey.'-'.$customerForCorrectionModal->id)
                    )
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('service-order::messages.equipment_type_document_preview_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="icon-base ti fs-3 text-primary" :class="documentPreview.icon || 'ti-file-text'"></i>
                    <div>
                        <div class="fw-semibold" x-text="documentPreview.title || '{{ trans('service-order::messages.equipment_type_document_without_title') }}'"></div>
                        <small class="text-body-secondary" x-text="documentPreview.documentTypeLabel || '-'">&nbsp;</small>
                    </div>
                </div>

                <template x-if="documentPreview.previewText">
                    <pre class="mb-0 p-3 bg-lighter rounded text-body small" x-text="documentPreview.previewText"></pre>
                </template>

                <template x-if="documentPreview.previewUrl">
                    <iframe :src="documentPreview.previewUrl" :title="documentPreview.title" class="w-100 border-0 rounded" height="520"></iframe>
                </template>

                <template x-if="!documentPreview.previewUrl && !documentPreview.previewText">
                    <div class="alert alert-secondary mb-0">{{ trans('service-order::messages.equipment_type_pending_file_preview') }}</div>
                </template>
            </div>
        </div>
    </div>
</div>

@if ($showEquipmentTypeModal)
    <div class="modal fade show d-block" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.equipment_type_edit_modal_title') }}</h5>
                    <button type="button" class="btn-close" wire:click="closeEquipmentTypeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        {{ trans('service-order::messages.equipment_type_edit_help') }}
                    </div>

                    <label class="form-label" for="equipmentTypeDraftId">{{ trans('service-order::messages.equipment_type') }}</label>
                    <select
                        id="equipmentTypeDraftId"
                        class="form-select @error('equipmentTypeDraftId') is-invalid @enderror"
                        wire:model="equipmentTypeDraftId"
                    >
                        <option value="">{{ trans('service-order::messages.select') }}</option>
                        @foreach ($equipmentTypes as $equipmentType)
                            <option value="{{ $equipmentType->id }}">{{ $equipmentType->name }}</option>
                        @endforeach
                    </select>
                    @error('equipmentTypeDraftId')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="closeEquipmentTypeModal">{{ trans('service-order::messages.cancel') }}</button>
                    <button type="button" class="btn btn-primary" wire:click="saveEquipmentType">{{ trans('service-order::messages.update') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@script
    <script>
        $wire.on('customer-correction-saved', () => {
            const modalEl = document.getElementById('customerCorrectionModal');

            if (!modalEl || !window.bootstrap) {
                return;
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        });
    </script>
@endscript

@if ($showDiscountModal)
    <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.edit_discount') }}</h5>
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
