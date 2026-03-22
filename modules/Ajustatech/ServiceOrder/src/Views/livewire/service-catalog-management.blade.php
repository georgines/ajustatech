@if (!$embedded)
    <x-slot name="page_title">{{ $title }}</x-slot>
@endif

<div>
    <form wire:submit.prevent="save">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nome do servico</label>
                        <input class="form-control" type="text" wire:model.defer="name" placeholder="Ex.: Analise de notebook">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label">Valor base</label>
                        <input class="form-control" type="number" step="0.01" min="0" wire:model.defer="base_price">
                        @error('base_price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" wire:model.defer="is_active">
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Descricao</label>
                        <textarea class="form-control" rows="3" wire:model.defer="description" placeholder="Descricao opcional"></textarea>
                        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label d-block">Salvar para uso depois</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" wire:model.defer="is_reusable">
                            <label class="form-check-label">Disponivel para futuras ordens</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Procedimentos (checklist em sequencia)</h5>
            <button class="btn btn-outline-primary" type="button" wire:click="addStep" wire:loading.attr="disabled">
                Adicionar procedimento
            </button>
        </div>

        @error('steps')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @foreach ($steps as $index => $step)
            <div class="card mb-3" wire:key="service-step-{{ $step['temp_id'] ?? $index }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="fw-semibold">Procedimento #{{ $index + 1 }}</div>
                        <div class="d-flex gap-1">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    wire:click="moveStepUp({{ $index }})"
                                    wire:loading.attr="disabled"
                                    @disabled($index === 0)>
                                <i class="ti ti-arrow-up"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    wire:click="moveStepDown({{ $index }})"
                                    wire:loading.attr="disabled"
                                    @disabled($index === count($steps) - 1)>
                                <i class="ti ti-arrow-down"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    wire:click="removeStep({{ $index }})"
                                    wire:loading.attr="disabled">
                                Remover
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label class="form-label">Nome do campo/procedimento</label>
                            <input class="form-control" type="text" wire:model.defer="steps.{{ $index }}.name">
                            @error('steps.' . $index . '.name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12 col-md-1">
                            <label class="form-label">Ordem</label>
                            <input class="form-control" type="number" wire:model.defer="steps.{{ $index }}.sort_order" disabled>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Campo de ajuda</label>
                            <input class="form-control" type="text" wire:model.defer="steps.{{ $index }}.help_text">
                            @error('steps.' . $index . '.help_text') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Campo de relato tecnico</label>
                            <input class="form-control" type="text" wire:model.defer="steps.{{ $index }}.technician_report_label">
                            @error('steps.' . $index . '.technician_report_label') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" wire:model.defer="steps.{{ $index }}.is_required">
                                <label class="form-check-label">Obrigatorio</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" wire:model.defer="steps.{{ $index }}.requires_image_proof">
                                <label class="form-check-label">Solicitar imagens de prova</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" wire:model.defer="steps.{{ $index }}.is_active">
                                <label class="form-check-label">Ativo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">Salvar</button>
        </div>
    </form>
</div>
