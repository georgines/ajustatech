<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label">{{ trans('financial::messages.payment_method_type') }}</label>
                    <select class="form-select" wire:model.live="type">
                        @foreach ($types as $item)
                            <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    @error('type') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-8">
                    <label class="form-label">{{ trans('financial::messages.payment_method_name') }}</label>
                    <input class="form-control" type="text" wire:model="name">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="m-0">{{ trans('financial::messages.payment_method_add_cost') }}</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">{{ trans('financial::messages.payment_method_fixed_cost') }}</label>
                    <input class="form-control" type="number" step="0.01" min="0" wire:model="fixedCost">
                    @error('fixedCost') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">{{ trans('financial::messages.payment_method_percent_cost') }}</label>
                    <input class="form-control" type="number" step="0.01" min="0" wire:model="percentCost">
                    @error('percentCost') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                @if ($this->isCardType())
                    <div class="col-12 col-md-2">
                        <label class="form-label">{{ trans('financial::messages.payment_method_brand') }}</label>
                        <select class="form-select" wire:model="brand">
                            <option value="">{{ trans('financial::messages.select_option') }}</option>
                            @foreach ($brands as $brandOption)
                                <option value="{{ $brandOption }}">{{ $brandOption }}</option>
                            @endforeach
                        </select>
                        @error('brand') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">{{ trans('financial::messages.payment_method_installments') }}</label>
                        <select class="form-select" wire:model="installments">
                            <option value="">{{ trans('financial::messages.select_option') }}</option>
                            @foreach ($installmentOptions as $option)
                                <option value="{{ $option }}">{{ $option }}x</option>
                            @endforeach
                        </select>
                        @error('installments') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">{{ trans('financial::messages.payment_method_receipt_channel') }}</label>
                        <select class="form-select" wire:model="receiptChannel">
                            <option value="">{{ trans('financial::messages.select_option') }}</option>
                            @foreach ($receiptChannels as $channel)
                                <option value="{{ $channel }}">{{ $channel }}</option>
                            @endforeach
                        </select>
                        @error('receiptChannel') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                @endif
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-outline-primary me-2" type="button" wire:click="addCost">
                        {{ $editingCostIndex !== null ? trans('financial::messages.update') : trans('financial::messages.payment_method_add_cost_button') }}
                    </button>
                    @if ($editingCostIndex !== null)
                        <button class="btn btn-outline-secondary" type="button" wire:click="cancelCostEdition">
                            {{ trans('financial::messages.cancel') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="m-0">{{ trans('financial::messages.payment_method_costs') }}</h5></div>
        <div class="card-body">
            @error('costs') <div class="alert alert-danger">{{ $message }}</div> @enderror

            @forelse ($costs as $cost)
                <div class="border rounded p-2 mb-2 small">
                    Fixo: R$ {{ number_format((float) $cost['fixed_cost'], 2, ',', '.') }} |
                    %: {{ number_format((float) $cost['percent_cost'], 2, ',', '.') }}
                    @if ($cost['brand']) | {{ trans('financial::messages.payment_method_brand') }}: {{ $cost['brand'] }} @endif
                    @if ($cost['installments']) | {{ trans('financial::messages.payment_method_installments') }}: {{ $cost['installments'] }} @endif
                    @if ($cost['receipt_channel']) | {{ trans('financial::messages.payment_method_receipt_channel') }}: {{ $cost['receipt_channel'] }} @endif
                    <button type="button"
                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill p-0 ms-2"
                        wire:click="editCost({{ $loop->index }})"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ trans('financial::messages.edit') }}"
                        aria-label="{{ trans('financial::messages.edit') }}">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill p-0 ms-2"
                        wire:click="duplicateCost({{ $loop->index }})"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ trans('financial::messages.duplicate') }}"
                        aria-label="{{ trans('financial::messages.duplicate') }}">
                        <i class="ti ti-copy"></i>
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-icon btn-text-secondary rounded-pill text-danger p-0 ms-2"
                        wire:click="confirmRemoveCost({{ $loop->index }})"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ trans('financial::messages.delete') }}"
                        aria-label="{{ trans('financial::messages.delete') }}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            @empty
                <p class="text-muted mb-0">{{ trans('financial::messages.no_records') }}</p>
            @endforelse

            <div class="mt-3 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" wire:click="save">
                    {{ $mode === 'edit' ? trans('financial::messages.update') : trans('financial::messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
