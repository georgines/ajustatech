<x-slot name="page_title">{{ $title }}</x-slot>
<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label">{{ trans('financial::messages.payment_method_brand') }}</label>
                <input type="text" class="form-control" wire:model="name">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="button" class="btn btn-primary" wire:click="save">
                    {{ $mode === 'edit' ? trans('financial::messages.update') : trans('financial::messages.save') }}
                </button>
            </div>
        </div>
    </div>
</div>