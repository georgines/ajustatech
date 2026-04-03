<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Nome</label>
                    <input class="form-control" type="text" wire:model="name">
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" wire:model="is_active">
                        <label class="form-check-label">Ativo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Descricao</label>
                    <textarea class="form-control" rows="2" wire:model="description"></textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Imagem do tipo de equipamento</label>
                    <input class="form-control" type="file" wire:model="image" accept="{{ $imageAccept }}">
                    <small class="text-muted">Formatos permitidos: {{ $imageAccept }}</small>
                    @error('image') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                @if ($currentImageUrl)
                    <div class="col-12 col-md-6">
                        <label class="form-label d-block">Imagem atual</label>
                        <img src="{{ $currentImageUrl }}" alt="Imagem do tipo de equipamento" class="rounded border mb-2" style="max-height: 140px;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="removeImage" id="removeImageSwitch">
                            <label class="form-check-label" for="removeImageSwitch">Remover imagem atual</label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Campos dinamicos</h5>
        <button class="btn btn-outline-primary" type="button" wire:click="addField">Adicionar campo</button>
    </div>

    @error('fields') <div class="alert alert-danger">{{ $message }}</div> @enderror

    @foreach ($fields as $index => $field)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div class="fw-semibold">Campo #{{ $index + 1 }}</div>
                    <div class="d-flex gap-1">
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                wire:click="moveFieldUp({{ $index }})"
                                @disabled($index === 0)
                                title="Mover para cima"
                                aria-label="Mover para cima">
                            <i class="ti ti-arrow-up"></i>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                wire:click="moveFieldDown({{ $index }})"
                                @disabled($index === count($fields) - 1)
                                title="Mover para baixo"
                                aria-label="Mover para baixo">
                            <i class="ti ti-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeField({{ $index }})">Remover</button>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tipo de campo</label>
                        <select class="form-select" wire:model.live="fields.{{ $index }}.field_type">
                            @foreach ($fieldTypes as $type)
                                <option value="{{ $type }}">{{ $fieldTypeLabels[$type] ?? $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Nome</label>
                        <input class="form-control" type="text" wire:model="fields.{{ $index }}.name">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label">Slug</label>
                        <input class="form-control" type="text" wire:model="fields.{{ $index }}.slug" placeholder="auto se vazio">
                    </div>
                    <div class="col-12 col-md-1">
                        <label class="form-label">Ordem</label>
                        <input class="form-control" type="number" wire:model="fields.{{ $index }}.sort_order" disabled>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label d-block">Regras</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="fields.{{ $index }}.is_required">
                            <label class="form-check-label">Obrigatorio</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="fields.{{ $index }}.is_printable">
                            <label class="form-check-label">Imprimir</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="fields.{{ $index }}.is_active">
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>

                    @if (in_array($field['field_type'], ['select', 'radio']))
                        <div class="col-12">
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Opcoes</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addOption({{ $index }})">Adicionar opcao</button>
                                </div>
                                @foreach (($field['options'] ?? []) as $optionIndex => $option)
                                    <div class="row g-2 mb-2">
                                        <div class="col-12 col-md-5">
                                            <input class="form-control" type="text" wire:model="fields.{{ $index }}.options.{{ $optionIndex }}.label" placeholder="Label">
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <input class="form-control" type="text" wire:model="fields.{{ $index }}.options.{{ $optionIndex }}.value" placeholder="Value">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeOption({{ $index }}, {{ $optionIndex }})">Remover</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($field['field_type'] === 'text')
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Placeholder</label>
                                    <input class="form-control" type="text" wire:model="fields.{{ $index }}.configuration.placeholder">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Ajuda</label>
                                    <input class="form-control" type="text" wire:model="fields.{{ $index }}.configuration.help">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Max caracteres</label>
                                    <input class="form-control" type="number" wire:model="fields.{{ $index }}.configuration.max_length">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($field['field_type'] === 'photo')
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Max fotos</label>
                                    <input class="form-control" type="number" wire:model="fields.{{ $index }}.configuration.max_files">
                                </div>
                                <div class="col-12 col-md-9">
                                    <label class="form-label">Extensoes permitidas (csv)</label>
                                    <input class="form-control" type="text" value="{{ implode(',', $field['configuration']['allowed_extensions'] ?? []) }}" disabled>
                                    <small class="text-muted">Padrao atual: jpg,jpeg,png</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($field['field_type'] === 'file')
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Modo de preview</label>
                                    <select class="form-select" wire:model="fields.{{ $index }}.configuration.preview_mode">
                                        <option value="modal">modal</option>
                                        <option value="new_tab">new_tab</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Extensoes permitidas (csv)</label>
                                    <input class="form-control" type="text" value="{{ implode(',', $field['configuration']['allowed_extensions'] ?? []) }}" disabled>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($field['field_type'] === 'document')
                        <div class="col-12">
                            <label class="form-label">Template do documento</label>
                            <textarea class="form-control" rows="4" wire:model="fields.{{ $index }}.configuration.template"></textarea>
                            <small class="text-muted">Variaveis: @{{cliente_nome}}, @{{equipamento_nome}}, @{{marca}}, @{{modelo}}, @{{numero_serie}}, @{{data_entrada}}, @{{defeito_relatado}}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">Salvar</button>
    </div>
</div>
