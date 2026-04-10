<x-slot name="page_title">{{ $title }}</x-slot>
<div
    x-data="{
        confirmDelete(id) {
            const runDelete = () => $wire.deleteEquipmentType(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: @js(trans('service-order::messages.equipment_type_confirm_delete')),
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

            if (confirm(@js(trans('service-order::messages.equipment_type_confirm_delete')))) {
                runDelete();
            }
        },
        confirmDuplicate(id) {
            const runDuplicate = () => $wire.duplicateEquipmentType(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: @js(trans('service-order::messages.equipment_type_confirm_duplicate')),
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

            if (confirm(@js(trans('service-order::messages.equipment_type_confirm_duplicate')))) {
                runDuplicate();
            }
        },
        confirmChangeStatus(id) {
            const runToggle = () => $wire.toggleEquipmentTypeStatus(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'question',
                    title: @js(trans('service-order::messages.equipment_type_confirm_status_change')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runToggle();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.equipment_type_confirm_status_change')))) {
                runToggle();
            }
        }
    }">
    <div class="card mb-3">
        <div class="card-header header-elements">
            <span class="me-2">{{ trans('service-order::messages.filters') }}</span>
            <div class="card-header-elements ms-auto">
                <a class="btn btn-primary" href="{{ route('service-order-equipment-types-create') }}">
                    <span class="tf-icon ti ti-plus ti-xs me-1"></span>{{ trans('service-order::messages.new_equipment_type') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="switch">
                        <input wire:model.live="activeOnly" class="switch-input" type="checkbox" />
                        <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                        </span>
                        <span class="switch-label">{{ trans('service-order::messages.only_active') }}</span>
                    </label>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label" for="equipmentTypeLimit">{{ trans('service-order::messages.records_per_page') }}</label>
                    <select id="equipmentTypeLimit" class="form-select" wire:model.live="limitePerPage">
                        <option value="10">10</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-12 col-md-8 mb-3">
                    <label class="form-label" for="equipmentTypeSearch">{{ trans('service-order::messages.search') }}</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input
                            id="equipmentTypeSearch"
                            type="text"
                            class="form-control"
                            placeholder="{{ trans('service-order::messages.equipment_type_search_placeholder') }}"
                            wire:model.live.debounce.400ms="search">
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
                        <th>{{ trans('service-order::messages.equipment_type_name') }}</th>
                        <th>{{ trans('service-order::messages.equipment_type_documents_count') }}</th>
                        <th>{{ trans('service-order::messages.equipment_type_fields_count') }}</th>
                        <th>{{ trans('service-order::messages.status') }}</th>
                        <th>{{ trans('service-order::messages.actions') }}</th>
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
                            <td>{{ (int) $equipmentType->documents_count }}</td>
                            <td>{{ (int) $equipmentType->fields_count }}</td>
                            <td>
                                <button type="button"
                                    class="btn p-0 border-0 bg-transparent"
                                    x-on:click="confirmChangeStatus('{{ $equipmentType->id }}')"
                                    title="{{ trans('service-order::messages.status') }}"
                                    aria-label="{{ trans('service-order::messages.status') }}">
                                    @if ($equipmentType->is_active)
                                        <span class="badge bg-label-success">{{ trans('service-order::messages.active') }}</span>
                                    @else
                                        <span class="badge bg-label-secondary">{{ trans('service-order::messages.inactive') }}</span>
                                    @endif
                                </button>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-equipment-types-edit', ['id' => $equipmentType->id]) }}"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>

                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDuplicate('{{ $equipmentType->id }}')"
                                    title="{{ trans('service-order::messages.duplicate') }}"
                                    aria-label="{{ trans('service-order::messages.duplicate') }}">
                                    <i class="text-primary ti ti-copy"></i>
                                </button>

                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDelete('{{ $equipmentType->id }}')"
                                    title="{{ trans('service-order::messages.delete') }}"
                                    aria-label="{{ trans('service-order::messages.delete') }}">
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
    </div>
</div>
