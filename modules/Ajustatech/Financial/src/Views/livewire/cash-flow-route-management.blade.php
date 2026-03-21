<x-slot name="page_title">{{ $title }}</x-slot>
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">{{ trans('financial::messages.cash_route_flow') }}</label>
                <select class="form-select" wire:model.live="flowKey">
                    <option value="">{{ trans('financial::messages.select_option') }}</option>
                    @foreach ($flowOptions as $flow)
                        <option value="{{ $flow['key'] }}">{{ $flow['label'] }}</option>
                    @endforeach
                </select>
                @error('flowKey') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            @if ($this->requiresPaymentMethodType())
                <div class="col-12 col-md-4">
                    <label class="form-label">{{ trans('financial::messages.payment_method_type') }}</label>
                    <select class="form-select" wire:model.live="paymentMethodType">
                        <option value="">{{ trans('financial::messages.select_option') }}</option>
                        @foreach ($paymentMethodTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('paymentMethodType') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            @endif

            <div class="col-12 col-md-4">
                <label class="form-label">{{ $this->cashFieldLabel() }}</label>
                <select class="form-select" wire:model="companyCashId">
                    <option value="">{{ trans('financial::messages.select_option') }}</option>
                    @foreach ($managerialCashes as $cash)
                        <option value="{{ $cash['id'] }}">{{ $cash['cash_name'] }}</option>
                    @endforeach
                </select>
                @error('companyCashId') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" wire:click="save">
                    {{ $mode === 'edit' ? trans('financial::messages.update') : trans('financial::messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
