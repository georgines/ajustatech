@if (!$embedded)
    <x-slot name="page_title">{{ $title }}</x-slot>
@endif
<div>
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Nome do servico</label>
                    <input class="form-control" type="text" wire:model="name">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Valor base</label>
                    <input class="form-control" type="number" step="0.01" min="0" wire:model="base_price">
                    @error('base_price') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" wire:model="is_active">
                        <label class="form-check-label">Ativo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Descricao</label>
                    <textarea class="form-control" rows="3" wire:model="description"></textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-primary" type="button" wire:click="save">Salvar</button>
            </div>
        </div>
    </div>
</div>
