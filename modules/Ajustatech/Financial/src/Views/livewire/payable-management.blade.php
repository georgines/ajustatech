<x-slot name="page_title">{{ $title }}</x-slot>
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label">{{ trans('financial::messages.counterparty') }}</label>
                <input type="text" class="form-control" wire:model="counterpartyName">
                @error('counterpartyName') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">{{ trans('financial::messages.amount') }}</label>
                <input type="number" step="0.01" min="0.01" class="form-control" wire:model="amount">
                @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">{{ trans('financial::messages.due_date') }}</label>
                <input type="date" class="form-control" wire:model="dueDate">
                @error('dueDate') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12">
                <label class="form-label">{{ trans('financial::messages.description') }}</label>
                <textarea class="form-control" wire:model="description"></textarea>
                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" wire:click="save">{{ trans('financial::messages.save') }}</button>
            </div>
        </div>
    </div>
</div>