<x-slot name="page_title">{{ $title }}</x-slot>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="m-0">{{ trans('financial::messages.sales_cash_open_title') }}</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">{{ trans('financial::messages.managerial_cash') }}</label>
                    <select class="form-select" wire:model="managerialCashId">
                        <option value="">{{ trans('financial::messages.select_option') }}</option>
                        @foreach ($managerialCashes as $cash)
                            <option value="{{ $cash->id }}">{{ $cash->cash_name }}</option>
                        @endforeach
                    </select>
                    @error('managerialCashId') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ trans('financial::messages.opening_amount') }}</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" wire:model="openingAmount">
                    @error('openingAmount') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <button type="button" class="btn btn-primary" wire:click="openCash">{{ trans('financial::messages.open_cash') }}</button>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="m-0">{{ trans('financial::messages.sales_cash_close_title') }}</h5></div>
            <div class="card-body">
                @if ($currentSession)
                    <p class="mb-1">{{ trans('financial::messages.current_session_opening') }}: <strong>R$ {{ number_format((float) $currentSession->opening_amount, 2, ',', '.') }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">{{ trans('financial::messages.closing_amount') }}</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" wire:model="closingAmount">
                        @error('closingAmount') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <button type="button" class="btn btn-success" wire:click="closeCash">{{ trans('financial::messages.close_cash') }}</button>
                @else
                    <p class="text-muted mb-0">{{ trans('financial::messages.sales_cash_not_opened_today') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>