<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    @if ($currentQuestion)
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">{{ $currentQuestion['section_name'] }}</div>
                    <small class="text-muted">Pergunta {{ $currentPosition }} de {{ $totalQuestions }}</small>
                </div>
                <div class="progress" style="width: 280px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ ($currentPosition / max($totalQuestions, 1)) * 100 }}%;"></div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5 class="mb-1">{{ $currentQuestion['prompt'] }}</h5>
                @if (!empty($currentQuestion['help_text']))
                    <div class="text-muted mb-3">{{ $currentQuestion['help_text'] }}</div>
                @endif

                @php
                    $qid = (string) $currentQuestion['id'];
                    $answerType = $currentQuestion['answer_type'];
                @endphp

                @if (in_array($answerType, ['single_select', 'yes_no', 'radio']))
                    <div class="d-flex flex-column gap-2">
                        @foreach ($currentQuestion['options'] as $option)
                            <label class="form-check">
                                <input class="form-check-input" type="radio" wire:model.defer="answers.{{ $qid }}" value="{{ $option['value'] }}">
                                <span class="form-check-label">{{ $option['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif ($answerType === 'multi_select')
                    <select class="form-select" multiple wire:model.defer="answers.{{ $qid }}">
                        @foreach ($currentQuestion['options'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                @elseif ($answerType === 'number')
                    <input class="form-control" type="number" step="0.01" wire:model.defer="answers.{{ $qid }}">
                @elseif ($answerType === 'date')
                    <input class="form-control" type="date" wire:model.defer="answers.{{ $qid }}">
                @elseif ($answerType === 'boolean')
                    <select class="form-select" wire:model.defer="answers.{{ $qid }}">
                        <option value="">Selecione</option>
                        <option value="1">Sim</option>
                        <option value="0">Nao</option>
                    </select>
                @elseif (in_array($answerType, ['photo', 'file']))
                    <input class="form-control mb-2" type="file" wire:model="questionUploads.{{ $qid }}" multiple>
                    <textarea class="form-control" rows="2" wire:model.defer="answers.{{ $qid }}" placeholder="Observacao tecnica da evidencia"></textarea>
                @else
                    <textarea class="form-control" rows="3" wire:model.defer="answers.{{ $qid }}"></textarea>
                @endif

                @if (!empty($currentQuestion['complementary_fields']))
                    <hr>
                    <h6>Campos complementares</h6>

                    @foreach ($currentQuestion['complementary_fields'] as $field)
                        @php
                            $fieldId = (string) $field['id'];
                            $state = $fieldStates[$fieldId] ?? ['visible' => true, 'required' => false];
                        @endphp
                        @if ($state['visible'])
                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $field['label'] }}
                                    @if ($state['required']) <span class="text-danger">*</span> @endif
                                </label>

                                @if ($field['field_type'] === 'number')
                                    <input class="form-control" type="number" step="0.01" wire:model.defer="complementaryAnswers.{{ $qid }}.{{ $fieldId }}">
                                @elseif ($field['field_type'] === 'date')
                                    <input class="form-control" type="date" wire:model.defer="complementaryAnswers.{{ $qid }}.{{ $fieldId }}">
                                @elseif ($field['field_type'] === 'boolean')
                                    <select class="form-select" wire:model.defer="complementaryAnswers.{{ $qid }}.{{ $fieldId }}">
                                        <option value="">Selecione</option>
                                        <option value="1">Sim</option>
                                        <option value="0">Nao</option>
                                    </select>
                                @elseif (in_array($field['field_type'], ['photo', 'file']))
                                    <input class="form-control" type="file" wire:model="complementaryUploads.{{ $qid }}.{{ $fieldId }}" multiple>
                                @else
                                    <textarea class="form-control" rows="2" wire:model.defer="complementaryAnswers.{{ $qid }}.{{ $fieldId }}"></textarea>
                                @endif
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <button type="button"
                    class="btn btn-outline-secondary"
                    wire:click="previousQuestion"
                    @disabled($currentPosition <= 1)>
                Voltar
            </button>

            @if ($isLastQuestion)
                <button type="button" class="btn btn-success" wire:click="finalizeAnalysis">Finalizar analise</button>
            @else
                <button type="button" class="btn btn-primary" wire:click="nextQuestion">Proxima pergunta</button>
            @endif
        </div>
    @else
        <div class="alert alert-warning">Nao ha perguntas configuradas para esta analise.</div>
    @endif
</div>

