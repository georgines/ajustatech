@if (!$embedded)
    <x-slot name="page_title">{{ $title }}</x-slot>
@endif

<div>
    <form wire:submit.prevent="save">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nome do tipo de analise</label>
                        <input class="form-control" type="text" wire:model.defer="name" placeholder="Ex.: Analise de notebook">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label">Descricao</label>
                        <input class="form-control" type="text" wire:model.defer="description" placeholder="Modelo tecnico padrao">
                        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" wire:model.defer="is_active">
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Secoes e perguntas tecnicas</h5>
            <button class="btn btn-outline-primary" type="button" wire:click="addSection">Adicionar secao</button>
        </div>

        @error('sections')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @foreach ($sections as $sectionIndex => $section)
            <div class="card mb-4" wire:key="section-{{ $section['temp_id'] }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2 align-items-center">
                        <strong>Secao #{{ $sectionIndex + 1 }}</strong>
                        <input class="form-control form-control-sm" style="min-width: 240px" type="text" wire:model.defer="sections.{{ $sectionIndex }}.name" placeholder="Nome da secao">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeSection({{ $sectionIndex }})">Remover secao</button>
                </div>

                <div class="card-body">
                    @foreach (($section['questions'] ?? []) as $questionIndex => $question)
                        <div class="border rounded p-3 mb-3" wire:key="question-{{ $question['temp_id'] }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">Pergunta #{{ $questionIndex + 1 }}</div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addQuestion({{ $sectionIndex }})">+ Pergunta</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeQuestion({{ $sectionIndex }}, {{ $questionIndex }})">Remover</button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-5">
                                    <label class="form-label">Pergunta tecnica</label>
                                    <input class="form-control" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.prompt">
                                    @error('sections.' . $sectionIndex . '.questions.' . $questionIndex . '.prompt') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-12 col-md-2">
                                    <label class="form-label">Tipo de resposta</label>
                                    <select class="form-select" wire:model.live="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.answer_type">
                                        <option value="text">Texto</option>
                                        <option value="number">Numero</option>
                                        <option value="date">Data</option>
                                        <option value="boolean">Booleano</option>
                                        <option value="single_select">Selecao unica</option>
                                        <option value="multi_select">Multipla escolha</option>
                                        <option value="radio">Radio</option>
                                        <option value="yes_no">Sim/nao</option>
                                        <option value="photo">Foto</option>
                                        <option value="file">Arquivo</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Ajuda para o tecnico</label>
                                    <input class="form-control" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.help_text">
                                </div>

                                <div class="col-12 col-md-2">
                                    <label class="form-label">Rotulo de observacao</label>
                                    <input class="form-control" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.technician_note_label">
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.is_required">
                                        <label class="form-check-label">Obrigatorio</label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.requires_photo_evidence">
                                        <label class="form-check-label">Solicitar evidencia</label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" wire:model.live="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.is_repeatable">
                                        <label class="form-check-label">Repetivel</label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <label class="form-label">Limite repeticao</label>
                                    <input class="form-control" type="number" min="1" max="100" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.repeat_limit" @disabled(!($question['is_repeatable'] ?? false))>
                                </div>
                            </div>

                            @if (in_array($question['answer_type'] ?? 'text', ['single_select','multi_select','radio','yes_no']))
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Opcoes de resposta</h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addOption({{ $sectionIndex }}, {{ $questionIndex }})">+ Opcao</button>
                                </div>

                                @foreach (($question['options'] ?? []) as $optionIndex => $option)
                                    <div class="row g-2 mb-2" wire:key="option-{{ $option['temp_id'] }}">
                                        <div class="col-5">
                                            <input class="form-control form-control-sm" type="text" placeholder="Label" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.label">
                                        </div>
                                        <div class="col-5">
                                            <input class="form-control form-control-sm" type="text" placeholder="Valor" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.options.{{ $optionIndex }}.value">
                                        </div>
                                        <div class="col-2 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeOption({{ $sectionIndex }}, {{ $questionIndex }}, {{ $optionIndex }})">Remover</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Campos complementares</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addComplementaryField({{ $sectionIndex }}, {{ $questionIndex }})">+ Campo</button>
                            </div>

                            @foreach (($question['complementary_fields'] ?? []) as $fieldIndex => $field)
                                <div class="row g-2 mb-2" wire:key="cfield-{{ $field['temp_id'] }}">
                                    <div class="col-3">
                                        <input class="form-control form-control-sm" type="text" placeholder="Nome interno" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.complementary_fields.{{ $fieldIndex }}.name">
                                    </div>
                                    <div class="col-3">
                                        <input class="form-control form-control-sm" type="text" placeholder="Rotulo" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.complementary_fields.{{ $fieldIndex }}.label">
                                    </div>
                                    <div class="col-2">
                                        <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.complementary_fields.{{ $fieldIndex }}.field_type">
                                            <option value="text">Texto</option>
                                            <option value="number">Numero</option>
                                            <option value="date">Data</option>
                                            <option value="boolean">Booleano</option>
                                            <option value="photo">Foto</option>
                                            <option value="file">Arquivo</option>
                                            <option value="json">JSON</option>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.complementary_fields.{{ $fieldIndex }}.is_required">
                                            <label class="form-check-label">Obrig.</label>
                                        </div>
                                    </div>
                                    <div class="col-2 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeComplementaryField({{ $sectionIndex }}, {{ $questionIndex }}, {{ $fieldIndex }})">Remover</button>
                                    </div>
                                </div>
                            @endforeach

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Regras condicionais</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addConditionalRule({{ $sectionIndex }}, {{ $questionIndex }})">+ Regra</button>
                            </div>

                            @foreach (($question['conditional_rules'] ?? []) as $ruleIndex => $rule)
                                <div class="row g-2 mb-2" wire:key="rule-{{ $rule['temp_id'] }}">
                                    <div class="col-3">
                                        <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.conditional_rules.{{ $ruleIndex }}.expected_option_temp_id">
                                            <option value="">Opcao gatilho</option>
                                            @foreach (($question['options'] ?? []) as $option)
                                                <option value="{{ $option['temp_id'] }}">{{ $option['label'] ?: '-' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.conditional_rules.{{ $ruleIndex }}.operator">
                                            <option value="equals">igual</option>
                                            <option value="not_equals">diferente</option>
                                            <option value="contains">contem</option>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.conditional_rules.{{ $ruleIndex }}.target_field_temp_id">
                                            <option value="">Campo alvo</option>
                                            @foreach (($question['complementary_fields'] ?? []) as $field)
                                                <option value="{{ $field['temp_id'] }}">{{ $field['label'] ?: '-' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.conditional_rules.{{ $ruleIndex }}.effect">
                                            <option value="require">obrigar</option>
                                            <option value="optional">opcional</option>
                                            <option value="show">mostrar</option>
                                            <option value="hide">ocultar</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeConditionalRule({{ $sectionIndex }}, {{ $questionIndex }}, {{ $ruleIndex }})">Remover</button>
                                    </div>
                                </div>
                            @endforeach

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Consequencias tecnicas</h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addConsequence({{ $sectionIndex }}, {{ $questionIndex }})">+ Consequencia</button>
                            </div>

                            @foreach (($question['consequences'] ?? []) as $consequenceIndex => $consequence)
                                <div class="border rounded p-2 mb-2">
                                    <div class="row g-2">
                                        <div class="col-3">
                                            <label class="form-label small">Opcao gatilho</label>
                                            <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.expected_option_temp_id">
                                                <option value="">(qualquer)</option>
                                                @foreach (($question['options'] ?? []) as $option)
                                                    <option value="{{ $option['temp_id'] }}">{{ $option['label'] ?: '-' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            <label class="form-label small">Severidade</label>
                                            <select class="form-select form-select-sm" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.severity">
                                                <option value="low">Baixa</option>
                                                <option value="medium">Media</option>
                                                <option value="high">Alta</option>
                                                <option value="critical">Critica</option>
                                                <option value="very_critical">Muito critica</option>
                                                <option value="irreparable">Irreparavel</option>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small">Descricao tecnica</label>
                                            <input class="form-control form-control-sm" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.description">
                                        </div>
                                        <div class="col-3">
                                            <label class="form-label small">Acao tecnica</label>
                                            <input class="form-control form-control-sm" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.technical_action_name" placeholder="Ex.: Substituicao de carcaca">
                                        </div>
                                        <div class="col-8">
                                            <label class="form-label small">Recomendacao exibida ao tecnico</label>
                                            <input class="form-control form-control-sm" type="text" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.recommendation_text">
                                        </div>
                                        <div class="col-2">
                                            <div class="form-check form-switch mt-4">
                                                <input class="form-check-input" type="checkbox" wire:model.defer="sections.{{ $sectionIndex }}.questions.{{ $questionIndex }}.consequences.{{ $consequenceIndex }}.should_generate_budget">
                                                <label class="form-check-label">Gerar orcamento</label>
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-4" wire:click="removeConsequence({{ $sectionIndex }}, {{ $questionIndex }}, {{ $consequenceIndex }})">Remover</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">Salvar tipo de analise</button>
        </div>
    </form>
</div>
