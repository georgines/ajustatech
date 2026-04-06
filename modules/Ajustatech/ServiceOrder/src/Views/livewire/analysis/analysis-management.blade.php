<x-slot name="page_title">{{ $title }}</x-slot>

<div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ trans('service-order::messages.analysis_service_form_title') }}</h5>
            <a class="btn btn-label-secondary" href="{{ route('service-order-analyses-show') }}">
                {{ trans('service-order::messages.back_to_list') }}
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label">{{ trans('service-order::messages.analysis_service_name') }}</label>
                    <input class="form-control"
                        type="text"
                        maxlength="255"
                        wire:model.blur="name"
                        placeholder="{{ trans('service-order::messages.analysis_service_name_placeholder') }}">
                    @error('name') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label">{{ trans('service-order::messages.analysis_service_value') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input class="form-control"
                            type="number"
                            min="0"
                            max="999999.99"
                            step="0.01"
                            inputmode="decimal"
                            wire:model.blur="value">
                    </div>
                    @error('value') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">{{ trans('service-order::messages.analysis_service_description') }}</label>
                    <textarea class="form-control"
                        rows="3"
                        maxlength="1000"
                        wire:model.blur="description"
                        placeholder="{{ trans('service-order::messages.analysis_service_description_placeholder') }}"></textarea>
                    @error('description') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ trans('service-order::messages.analysis_questions') }}</h5>
        </div>
        <div class="card-body">
            @error('questions') <small class="text-danger d-block mb-3">{{ $message }}</small> @enderror

            <div class="d-flex flex-column gap-3">
                @foreach ($questions as $index => $question)
                    <div class="border rounded p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <strong>
                                    @if ($question['is_subquestion'] ?? false)
                                        {{ trans('service-order::messages.analysis_subquestion_title', ['sub' => $this->getSubquestionNumberInParent($index), 'question' => $this->getSubquestionParentMainNumber($index)]) }}
                                    @else
                                        {{ trans('service-order::messages.question') }} #{{ $this->getMainQuestionNumber($index) }}
                                    @endif
                                </strong>
                                <span class="badge bg-label-primary text-uppercase">{{ $question['question_type'] === 'yes_no' ? trans('service-order::messages.analysis_type_yes_no') : trans('service-order::messages.analysis_type_select') }}</span>
                                @if ($question['is_subquestion'] ?? false)
                                    @php $triggerLabel = $this->getSubquestionTriggerLabel($index); @endphp
                                    @if ($triggerLabel !== '')
                                        <span class="badge bg-label-secondary">{{ trans('service-order::messages.analysis_subquestion_when') }}: {{ $triggerLabel }}</span>
                                    @endif
                                @endif
                                @if ($question['is_required'] ?? false)
                                    <span class="badge bg-label-secondary">{{ trans('service-order::messages.analysis_question_required') }}</span>
                                @endif
                                @if ($question['is_technical_description_required'] ?? false)
                                    <span class="badge bg-label-secondary">{{ trans('service-order::messages.analysis_technical_description_required') }}</span>
                                @endif
                                @if ($question['is_image_required'] ?? false)
                                    <span class="badge bg-label-secondary">{{ trans('service-order::messages.analysis_images_required') }}</span>
                                    <span class="badge bg-label-secondary">{{ (int) ($question['required_images_count'] ?? 1) }}x</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                @if ($this->canMoveQuestionUp($index))
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="moveQuestionUp({{ $index }})" title="{{ trans('service-order::messages.move_up') }}" aria-label="{{ trans('service-order::messages.move_up') }}">
                                        <i class="text-primary ti ti-arrow-up"></i>
                                    </button>
                                @endif
                                @if ($this->canMoveQuestionDown($index))
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="moveQuestionDown({{ $index }})" title="{{ trans('service-order::messages.move_down') }}" aria-label="{{ trans('service-order::messages.move_down') }}">
                                        <i class="text-primary ti ti-arrow-down"></i>
                                    </button>
                                @endif
                                @if ($question['has_help'] ?? false)
                                    <button type="button" class="btn btn-sm btn-icon" wire:click="openQuestionHelpModal({{ $index }})" title="{{ trans('service-order::messages.analysis_question_help_button') }}" aria-label="{{ trans('service-order::messages.analysis_question_help_button') }}">
                                        <i class="text-primary ti ti-help"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-icon" wire:click="editQuestion({{ $index }})" title="{{ trans('service-order::messages.edit') }}" aria-label="{{ trans('service-order::messages.edit') }}">
                                    <i class="text-primary ti ti-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon" wire:click="removeQuestion({{ $index }})" title="{{ trans('service-order::messages.delete') }}" aria-label="{{ trans('service-order::messages.delete') }}">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ trans('service-order::messages.analysis_question_text') }}</label>
                                <input class="form-control"
                                    type="text"
                                    maxlength="500"
                                    data-question-text-input="{{ $index }}"
                                    wire:model.blur="questions.{{ $index }}.question_text"
                                    placeholder="{{ trans('service-order::messages.analysis_question_text_placeholder') }}">
                                @error('questions.'.$index.'.question_text') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>

                            @if (($question['question_type'] ?? '') === 'yes_no')
                                <div class="col-12">
                                    <label class="form-label">{{ trans('service-order::messages.analysis_answer_procedures') }}</label>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small">{{ trans('service-order::messages.confirm_yes') }}</label>
                                            <select class="form-select" wire:model.live="questions.{{ $index }}.answer_procedure_map.yes.procedure_id">
                                                <option value="">{{ trans('service-order::messages.analysis_no_procedure') }}</option>
                                                @foreach ($availableProcedures as $procedure)
                                                    <option value="{{ $procedure['id'] }}">{{ $procedure['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small">{{ trans('service-order::messages.confirm_no') }}</label>
                                            <select class="form-select" wire:model.live="questions.{{ $index }}.answer_procedure_map.no.procedure_id">
                                                <option value="">{{ trans('service-order::messages.analysis_no_procedure') }}</option>
                                                @foreach ($availableProcedures as $procedure)
                                                    <option value="{{ $procedure['id'] }}">{{ $procedure['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">{{ trans('service-order::messages.analysis_question_options') }}</label>
                                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addOption({{ $index }})" @disabled(count($question['options'] ?? []) >= 8)>
                                            {{ trans('service-order::messages.add_option') }}
                                        </button>
                                    </div>
                                    @foreach (($question['options'] ?? []) as $optionIndex => $option)
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-md-6">
                                                <input class="form-control"
                                                    type="text"
                                                    maxlength="120"
                                                    wire:model.blur="questions.{{ $index }}.options.{{ $optionIndex }}.label"
                                                    placeholder="{{ trans('service-order::messages.analysis_option_placeholder') }}">
                                            </div>
                                            <div class="col-10 col-md-5">
                                                <select class="form-select" wire:model.live="questions.{{ $index }}.options.{{ $optionIndex }}.procedure_id">
                                                    <option value="">{{ trans('service-order::messages.analysis_no_procedure') }}</option>
                                                    @foreach ($availableProcedures as $procedure)
                                                        <option value="{{ $procedure['id'] }}">{{ $procedure['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2 col-md-1">
                                                <button type="button" class="btn btn-sm btn-icon" wire:click="removeOption({{ $index }}, {{ $optionIndex }})">
                                                    <i class="text-primary ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    @error('questions.'.$index.'.options') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openCreateQuestionModal({{ max(-1, count($questions) - 1) }})">
                    {{ trans('service-order::messages.add_question') }}
                </button>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-primary" wire:click="save">
            {{ $mode === 'edit' ? trans('service-order::messages.update') : trans('service-order::messages.save') }}
        </button>
    </div>

    <div wire:ignore.self class="modal fade" id="analysisAddQuestionTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $editingQuestionIndex === null ? trans('service-order::messages.analysis_add_question_modal_title') : trans('service-order::messages.edit') . ' ' . trans('service-order::messages.question') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="d-flex flex-column gap-3">
                        @php
                            $subquestionOptions = $editingQuestionIndex === null
                                ? $this->getNewSubquestionTriggerOptions()
                                : $this->getSubquestionTriggerOptionsForEdit($editingQuestionIndex);
                        @endphp
                        <div class="d-flex flex-column gap-2">
                            <label class="switch mb-0">
                                <input wire:model.live="newQuestionDraft.is_subquestion" class="switch-input" type="checkbox" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">{{ trans('service-order::messages.analysis_subquestion_from_previous') }}</span>
                            </label>
                            @if ($newQuestionDraft['is_subquestion'] ?? false)
                                <div>
                                    <label class="form-label">{{ trans('service-order::messages.analysis_question_condition_value') }}</label>
                                    <select class="form-select" wire:model.live="newQuestionDraft.condition_value">
                                        <option value="">{{ trans('service-order::messages.analysis_subquestion_trigger_placeholder') }}</option>
                                        @foreach ($subquestionOptions as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('newQuestionDraft.condition_value') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                </div>
                            @endif
                            <label class="switch mb-0">
                                <input wire:model.live="newQuestionDraft.is_required" class="switch-input" type="checkbox" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">{{ trans('service-order::messages.analysis_question_required') }}</span>
                            </label>
                            <label class="switch mb-0">
                                <input wire:model.live="newQuestionDraft.is_technical_description_required" class="switch-input" type="checkbox" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">{{ trans('service-order::messages.analysis_technical_description_required') }}</span>
                            </label>
                            <label class="switch mb-0">
                                <input wire:model.live="newQuestionDraft.is_image_required" class="switch-input" type="checkbox" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">{{ trans('service-order::messages.analysis_images_required') }}</span>
                            </label>
                            <label class="switch mb-0">
                                <input wire:model.live="newQuestionDraft.has_help" class="switch-input" type="checkbox" />
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"></span>
                                    <span class="switch-off"></span>
                                </span>
                                <span class="switch-label">{{ trans('service-order::messages.analysis_question_help_enabled') }}</span>
                            </label>
                        </div>

                        @if ($newQuestionDraft['is_image_required'] ?? false)
                            <div>
                                <label class="form-label">{{ trans('service-order::messages.analysis_required_images_count') }}</label>
                                <select class="form-select" wire:model.live="newQuestionDraft.required_images_count">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        @if ($editingQuestionIndex === null)
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button"
                                        class="btn btn-outline-primary w-100 py-4"
                                        wire:click="createQuestionFromModal('yes_no')">
                                        {{ trans('service-order::messages.analysis_type_yes_no') }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button"
                                        class="btn btn-outline-primary w-100 py-4"
                                        wire:click="createQuestionFromModal('select')">
                                    {{ trans('service-order::messages.analysis_type_select') }}
                                </button>
                            </div>
                        </div>
                        @else
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button"
                                        class="btn w-100 py-4 {{ ($newQuestionDraft['question_type'] ?? 'yes_no') === 'yes_no' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="setQuestionTypeOnEdit('yes_no')">
                                        {{ trans('service-order::messages.analysis_type_yes_no') }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button"
                                        class="btn w-100 py-4 {{ ($newQuestionDraft['question_type'] ?? 'yes_no') === 'select' ? 'btn-primary' : 'btn-outline-primary' }}"
                                        wire:click="setQuestionTypeOnEdit('select')">
                                        {{ trans('service-order::messages.analysis_type_select') }}
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary w-100" wire:click="saveQuestionOptionsFromModal">
                                {{ trans('service-order::messages.save') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="analysisQuestionHelpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('service-order::messages.analysis_question_help_button') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ trans('service-order::messages.analysis_question_help_content') }}</label>
                    <textarea class="form-control"
                        rows="5"
                        maxlength="2000"
                        wire:model.blur="questionHelpDraft.content"
                        placeholder="{{ trans('service-order::messages.analysis_question_help_content_placeholder') }}"></textarea>
                    @error('questionHelpDraft.content') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ trans('service-order::messages.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveQuestionHelpFromModal">
                        {{ trans('service-order::messages.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const addQuestionModalEl = document.getElementById('analysisAddQuestionTypeModal');
            const helpModalEl = document.getElementById('analysisQuestionHelpModal');
            if (!addQuestionModalEl) return;

            addQuestionModalEl.addEventListener('hidden.bs.modal', () => {
                $wire.resetQuestionModalState();
            });
            if (helpModalEl) {
                helpModalEl.addEventListener('hidden.bs.modal', () => {
                    $wire.resetQuestionHelpModalState();
                });
            }

            Livewire.on('analysis-question-added', () => {
                const instance = bootstrap.Modal.getOrCreateInstance(addQuestionModalEl);
                instance.hide();
            });

            Livewire.on('analysis-question-create-open-modal', () => {
                const instance = bootstrap.Modal.getOrCreateInstance(addQuestionModalEl);
                instance.show();
            });

            Livewire.on('analysis-question-edit-open-modal', () => {
                const instance = bootstrap.Modal.getOrCreateInstance(addQuestionModalEl);
                instance.show();
            });

            Livewire.on('analysis-question-options-saved', () => {
                const instance = bootstrap.Modal.getOrCreateInstance(addQuestionModalEl);
                instance.hide();
            });

            Livewire.on('analysis-question-help-open-modal', () => {
                if (!helpModalEl) return;
                const instance = bootstrap.Modal.getOrCreateInstance(helpModalEl);
                instance.show();
            });

            Livewire.on('analysis-question-help-saved', () => {
                if (!helpModalEl) return;
                const instance = bootstrap.Modal.getOrCreateInstance(helpModalEl);
                instance.hide();
            });
        });
    </script>
@endscript
