<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Ajustatech\ServiceOrderOld\Database\Models\AnalysisTechnicalAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceCatalogManagement extends Component
{
    public string $title = 'Cadastro de Tipo de Analise';
    public string $mode = 'create';
    public ?string $analysisTypeId = null;
    public bool $embedded = false;

    public string $name = '';
    public ?string $description = null;
    public bool $is_active = true;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $sections = [];

    public function mount(?string $id = null, bool $embedded = false): void
    {
        $this->embedded = $embedded;

        if ($id) {
            $this->loadAnalysisType($id);
            return;
        }

        $this->bootNewForm();
    }

    public function render()
    {
        return view('service-order::livewire.service-catalog-management');
    }

    public function addSection(): void
    {
        $this->sections[] = $this->makeEmptySection(count($this->sections) + 1);
    }

    public function removeSection(int $sectionIndex): void
    {
        if (!isset($this->sections[$sectionIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]);
        $this->sections = array_values($this->sections);

        if (empty($this->sections)) {
            $this->sections[] = $this->makeEmptySection(1);
        }

        $this->reindexSections();
    }

    public function addQuestion(int $sectionIndex): void
    {
        if (!isset($this->sections[$sectionIndex])) {
            return;
        }

        $questions = Arr::get($this->sections[$sectionIndex], 'questions', []);
        $questions[] = $this->makeEmptyQuestion(count($questions) + 1);
        $this->sections[$sectionIndex]['questions'] = $questions;
    }

    public function removeQuestion(int $sectionIndex, int $questionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]['questions'][$questionIndex]);
        $this->sections[$sectionIndex]['questions'] = array_values($this->sections[$sectionIndex]['questions']);

        if (empty($this->sections[$sectionIndex]['questions'])) {
            $this->sections[$sectionIndex]['questions'][] = $this->makeEmptyQuestion(1);
        }

        $this->reindexQuestions($sectionIndex);
    }

    public function addOption(int $sectionIndex, int $questionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex])) {
            return;
        }

        $options = Arr::get($this->sections[$sectionIndex]['questions'][$questionIndex], 'options', []);
        $options[] = $this->makeEmptyOption(count($options) + 1);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['options'] = $options;
    }

    public function removeOption(int $sectionIndex, int $questionIndex, int $optionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex]['options'][$optionIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]['questions'][$questionIndex]['options'][$optionIndex]);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['options'] = array_values(
            $this->sections[$sectionIndex]['questions'][$questionIndex]['options']
        );
    }

    public function addComplementaryField(int $sectionIndex, int $questionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex])) {
            return;
        }

        $fields = Arr::get($this->sections[$sectionIndex]['questions'][$questionIndex], 'complementary_fields', []);
        $fields[] = $this->makeEmptyComplementaryField(count($fields) + 1);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['complementary_fields'] = $fields;
    }

    public function removeComplementaryField(int $sectionIndex, int $questionIndex, int $fieldIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex]['complementary_fields'][$fieldIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]['questions'][$questionIndex]['complementary_fields'][$fieldIndex]);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['complementary_fields'] = array_values(
            $this->sections[$sectionIndex]['questions'][$questionIndex]['complementary_fields']
        );
    }

    public function addConditionalRule(int $sectionIndex, int $questionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex])) {
            return;
        }

        $rules = Arr::get($this->sections[$sectionIndex]['questions'][$questionIndex], 'conditional_rules', []);
        $rules[] = $this->makeEmptyRule(count($rules) + 1);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['conditional_rules'] = $rules;
    }

    public function removeConditionalRule(int $sectionIndex, int $questionIndex, int $ruleIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex]['conditional_rules'][$ruleIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]['questions'][$questionIndex]['conditional_rules'][$ruleIndex]);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['conditional_rules'] = array_values(
            $this->sections[$sectionIndex]['questions'][$questionIndex]['conditional_rules']
        );
    }

    public function addConsequence(int $sectionIndex, int $questionIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex])) {
            return;
        }

        $consequences = Arr::get($this->sections[$sectionIndex]['questions'][$questionIndex], 'consequences', []);
        $consequences[] = $this->makeEmptyConsequence();
        $this->sections[$sectionIndex]['questions'][$questionIndex]['consequences'] = $consequences;
    }

    public function removeConsequence(int $sectionIndex, int $questionIndex, int $consequenceIndex): void
    {
        if (!isset($this->sections[$sectionIndex]['questions'][$questionIndex]['consequences'][$consequenceIndex])) {
            return;
        }

        unset($this->sections[$sectionIndex]['questions'][$questionIndex]['consequences'][$consequenceIndex]);
        $this->sections[$sectionIndex]['questions'][$questionIndex]['consequences'] = array_values(
            $this->sections[$sectionIndex]['questions'][$questionIndex]['consequences']
        );
    }

    public function updatedSections(): void
    {
        $this->resetErrorBag();
    }

    public function save()
    {
        $this->validate($this->rules(), $this->messages());
        $this->validateQuestionStructure();

        DB::transaction(function (): void {
            $analysisType = $this->mode === 'edit' && $this->analysisTypeId
                ? AnalysisType::query()->findOrFail($this->analysisTypeId)
                : new AnalysisType();

            $analysisType->fill([
                'name' => trim($this->name),
                'description' => $this->normalizeNullableText($this->description),
                'is_active' => $this->is_active,
            ]);

            if (!$analysisType->exists || !$analysisType->slug) {
                $analysisType->slug = Str::slug($analysisType->name) . '-' . Str::lower((string) Str::uuid());
            }

            $analysisType->save();

            $analysisType->sections()->delete();

            foreach (collect($this->sections)->values() as $sectionIndex => $section) {
                $sectionModel = $analysisType->sections()->create([
                    'name' => trim((string) Arr::get($section, 'name')),
                    'sort_order' => $sectionIndex + 1,
                    'is_active' => (bool) Arr::get($section, 'is_active', true),
                ]);

                foreach (collect(Arr::get($section, 'questions', []))->values() as $questionIndex => $question) {
                    $questionModel = $sectionModel->questions()->create([
                        'code' => $this->normalizeNullableText(Arr::get($question, 'code')),
                        'prompt' => trim((string) Arr::get($question, 'prompt')),
                        'help_text' => $this->normalizeNullableText(Arr::get($question, 'help_text')),
                        'technician_note_label' => $this->normalizeNullableText(Arr::get($question, 'technician_note_label')),
                        'answer_type' => (string) Arr::get($question, 'answer_type', 'text'),
                        'sort_order' => $questionIndex + 1,
                        'is_required' => (bool) Arr::get($question, 'is_required', false),
                        'is_repeatable' => (bool) Arr::get($question, 'is_repeatable', false),
                        'requires_photo_evidence' => (bool) Arr::get($question, 'requires_photo_evidence', false),
                        'repeat_limit' => Arr::get($question, 'repeat_limit') ?: null,
                        'is_active' => (bool) Arr::get($question, 'is_active', true),
                    ]);

                    $optionMap = [];
                    foreach (collect(Arr::get($question, 'options', []))->values() as $optionIndex => $option) {
                        if (!$this->shouldPersistOption((string) $questionModel->answer_type, $option)) {
                            continue;
                        }

                        $optionModel = $questionModel->options()->create([
                            'label' => trim((string) Arr::get($option, 'label')),
                            'value' => trim((string) Arr::get($option, 'value')),
                            'sort_order' => $optionIndex + 1,
                            'is_active' => (bool) Arr::get($option, 'is_active', true),
                        ]);

                        $optionMap[(string) Arr::get($option, 'temp_id')] = $optionModel->id;
                    }

                    $fieldMap = [];
                    foreach (collect(Arr::get($question, 'complementary_fields', []))->values() as $fieldIndex => $field) {
                        $fieldModel = $questionModel->complementaryFields()->create([
                            'name' => trim((string) Arr::get($field, 'name')),
                            'label' => trim((string) Arr::get($field, 'label')),
                            'field_type' => (string) Arr::get($field, 'field_type', 'text'),
                            'sort_order' => $fieldIndex + 1,
                            'is_required' => (bool) Arr::get($field, 'is_required', false),
                            'is_active' => (bool) Arr::get($field, 'is_active', true),
                            'configuration' => Arr::get($field, 'configuration', []),
                        ]);

                        $fieldMap[(string) Arr::get($field, 'temp_id')] = $fieldModel->id;
                    }

                    foreach (collect(Arr::get($question, 'conditional_rules', []))->values() as $ruleIndex => $rule) {
                        $targetTempId = (string) Arr::get($rule, 'target_field_temp_id');
                        $expectedOptionTempId = (string) Arr::get($rule, 'expected_option_temp_id');
                        if (!isset($fieldMap[$targetTempId])) {
                            continue;
                        }

                        DB::table('analysis_conditional_rules')->insert([
                            'id' => (string) Str::uuid(),
                            'analysis_question_id' => $questionModel->id,
                            'target_type' => 'complementary_field',
                            'target_id' => $fieldMap[$targetTempId],
                            'operator' => Arr::get($rule, 'operator', 'equals'),
                            'expected_value' => $this->normalizeNullableText(Arr::get($rule, 'expected_value')),
                            'expected_option_id' => $optionMap[$expectedOptionTempId] ?? null,
                            'effect' => Arr::get($rule, 'effect', 'require'),
                            'effect_value' => null,
                            'sort_order' => $ruleIndex + 1,
                            'is_active' => (bool) Arr::get($rule, 'is_active', true),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    foreach (collect(Arr::get($question, 'consequences', []))->values() as $consequence) {
                        $description = trim((string) Arr::get($consequence, 'description', ''));
                        if ($description === '') {
                            continue;
                        }

                        $technicalActionId = $this->resolveTechnicalActionId(Arr::get($consequence, 'technical_action_name'));
                        $expectedOptionTempId = (string) Arr::get($consequence, 'expected_option_temp_id');

                        $questionModel->consequences()->create([
                            'analysis_question_option_id' => $optionMap[$expectedOptionTempId] ?? null,
                            'match_operator' => Arr::get($consequence, 'match_operator', 'equals'),
                            'match_value' => $this->normalizeNullableText(Arr::get($consequence, 'match_value')),
                            'severity' => Arr::get($consequence, 'severity', 'medium'),
                            'description' => $description,
                            'analysis_technical_action_id' => $technicalActionId,
                            'should_generate_budget' => (bool) Arr::get($consequence, 'should_generate_budget', true),
                            'visible_to_technician' => (bool) Arr::get($consequence, 'visible_to_technician', true),
                            'recommendation_text' => $this->normalizeNullableText(Arr::get($consequence, 'recommendation_text')),
                            'is_active' => (bool) Arr::get($consequence, 'is_active', true),
                        ]);
                    }
                }
            }

            $this->analysisTypeId = $analysisType->id;
            $this->mode = 'edit';
            $this->title = 'Editar Tipo de Analise';
        });

        if ($this->embedded) {
            $this->dispatch('service-catalog-changed');
            $this->dispatch('analysis-type-changed');
            $this->bootNewForm();
            return null;
        }

        return redirect()->route('service-order-analysis-types-show');
    }

    private function loadAnalysisType(string $id): void
    {
        $analysisType = AnalysisType::query()
            ->with([
                'sections' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions.options' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions.complementaryFields' => fn ($query) => $query->orderBy('sort_order'),
                'sections.questions.consequences.technicalAction',
            ])
            ->findOrFail($id);

        $this->mode = 'edit';
        $this->analysisTypeId = $analysisType->id;
        $this->title = 'Editar Tipo de Analise';
        $this->name = $analysisType->name;
        $this->description = $analysisType->description;
        $this->is_active = (bool) $analysisType->is_active;

        $this->sections = $analysisType->sections->map(function ($section, int $sectionIndex): array {
            return [
                'temp_id' => (string) Str::uuid(),
                'id' => $section->id,
                'name' => $section->name,
                'sort_order' => $sectionIndex + 1,
                'is_active' => (bool) $section->is_active,
                'questions' => $section->questions->map(function ($question, int $questionIndex): array {
                    $options = $question->options->map(function ($option, int $optionIndex): array {
                        return [
                            'temp_id' => (string) Str::uuid(),
                            'id' => $option->id,
                            'label' => $option->label,
                            'value' => $option->value,
                            'sort_order' => $optionIndex + 1,
                            'is_active' => (bool) $option->is_active,
                        ];
                    })->values()->all();

                    $fields = $question->complementaryFields->map(function ($field, int $fieldIndex): array {
                        return [
                            'temp_id' => (string) Str::uuid(),
                            'id' => $field->id,
                            'name' => $field->name,
                            'label' => $field->label,
                            'field_type' => $field->field_type,
                            'sort_order' => $fieldIndex + 1,
                            'is_required' => (bool) $field->is_required,
                            'is_active' => (bool) $field->is_active,
                            'configuration' => $field->configuration ?? [],
                        ];
                    })->values()->all();

                    $optionById = collect($options)->keyBy('id');
                    $fieldById = collect($fields)->keyBy('id');
                    $rules = DB::table('analysis_conditional_rules')
                        ->where('analysis_question_id', $question->id)
                        ->where('target_type', 'complementary_field')
                        ->orderBy('sort_order')
                        ->get()
                        ->map(function ($rule) use ($optionById, $fieldById): array {
                            return [
                                'temp_id' => (string) Str::uuid(),
                                'expected_option_temp_id' => Arr::get($optionById->get($rule->expected_option_id), 'temp_id'),
                                'operator' => $rule->operator,
                                'expected_value' => $rule->expected_value,
                                'target_field_temp_id' => Arr::get($fieldById->get($rule->target_id), 'temp_id'),
                                'effect' => $rule->effect,
                                'is_active' => (bool) $rule->is_active,
                            ];
                        })
                        ->values()
                        ->all();

                    $consequences = $question->consequences->map(function ($consequence) use ($optionById): array {
                        $action = $consequence->technicalAction;

                        return [
                            'expected_option_temp_id' => Arr::get($optionById->get($consequence->analysis_question_option_id), 'temp_id'),
                            'match_operator' => $consequence->match_operator,
                            'match_value' => $consequence->match_value,
                            'severity' => $consequence->severity,
                            'description' => $consequence->description,
                            'technical_action_name' => $action?->name,
                            'should_generate_budget' => (bool) $consequence->should_generate_budget,
                            'visible_to_technician' => (bool) $consequence->visible_to_technician,
                            'recommendation_text' => $consequence->recommendation_text,
                            'is_active' => (bool) $consequence->is_active,
                        ];
                    })->values()->all();

                    return [
                        'temp_id' => (string) Str::uuid(),
                        'id' => $question->id,
                        'code' => $question->code,
                        'prompt' => $question->prompt,
                        'help_text' => $question->help_text,
                        'technician_note_label' => $question->technician_note_label,
                        'answer_type' => $question->answer_type,
                        'sort_order' => $questionIndex + 1,
                        'is_required' => (bool) $question->is_required,
                        'is_repeatable' => (bool) $question->is_repeatable,
                        'repeat_limit' => $question->repeat_limit,
                        'requires_photo_evidence' => (bool) $question->requires_photo_evidence,
                        'is_active' => (bool) $question->is_active,
                        'options' => $options,
                        'complementary_fields' => $fields,
                        'conditional_rules' => $rules,
                        'consequences' => $consequences,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        if (empty($this->sections)) {
            $this->sections = [$this->makeEmptySection(1)];
        }
    }

    private function bootNewForm(): void
    {
        $this->mode = 'create';
        $this->analysisTypeId = null;
        $this->title = 'Cadastro de Tipo de Analise';
        $this->name = '';
        $this->description = null;
        $this->is_active = true;
        $this->sections = [$this->makeEmptySection(1)];
        $this->resetValidation();
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.name' => ['required', 'string', 'max:255'],
            'sections.*.is_active' => ['boolean'],
            'sections.*.questions' => ['required', 'array', 'min:1'],
            'sections.*.questions.*.prompt' => ['required', 'string', 'max:2000'],
            'sections.*.questions.*.answer_type' => ['required', 'in:text,number,date,boolean,single_select,multi_select,yes_no,radio,photo,file'],
            'sections.*.questions.*.is_required' => ['boolean'],
            'sections.*.questions.*.is_repeatable' => ['boolean'],
            'sections.*.questions.*.repeat_limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sections.*.questions.*.requires_photo_evidence' => ['boolean'],
            'sections.*.questions.*.is_active' => ['boolean'],
            'sections.*.questions.*.options' => ['array'],
            'sections.*.questions.*.options.*.label' => ['nullable', 'string', 'max:255'],
            'sections.*.questions.*.options.*.value' => ['nullable', 'string', 'max:255'],
            'sections.*.questions.*.complementary_fields' => ['array'],
            'sections.*.questions.*.complementary_fields.*.name' => ['nullable', 'string', 'max:255'],
            'sections.*.questions.*.complementary_fields.*.label' => ['nullable', 'string', 'max:255'],
            'sections.*.questions.*.complementary_fields.*.field_type' => ['nullable', 'in:text,number,date,boolean,photo,file,json'],
            'sections.*.questions.*.consequences' => ['array'],
            'sections.*.questions.*.consequences.*.description' => ['nullable', 'string', 'max:2000'],
            'sections.*.questions.*.consequences.*.severity' => ['nullable', 'in:low,medium,high,critical,very_critical,irreparable'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do tipo de analise.',
            'sections.required' => 'Adicione pelo menos uma secao.',
            'sections.*.name.required' => 'Informe o nome da secao.',
            'sections.*.questions.*.prompt.required' => 'Informe a pergunta tecnica.',
            'sections.*.questions.*.answer_type.required' => 'Informe o tipo de resposta da pergunta.',
        ];
    }

    private function validateQuestionStructure(): void
    {
        $errors = [];

        foreach ($this->sections as $sectionIndex => $section) {
            foreach (Arr::get($section, 'questions', []) as $questionIndex => $question) {
                $answerType = (string) Arr::get($question, 'answer_type', 'text');
                $questionPath = 'sections.' . $sectionIndex . '.questions.' . $questionIndex;

                if (in_array($answerType, ['single_select', 'multi_select', 'yes_no', 'radio'], true)) {
                    $validOptions = collect(Arr::get($question, 'options', []))
                        ->filter(fn (array $option) => trim((string) Arr::get($option, 'label')) !== '' && trim((string) Arr::get($option, 'value')) !== '')
                        ->values();

                    if ($validOptions->count() < 2) {
                        $errors[$questionPath . '.options'] = 'Perguntas de selecao precisam ter ao menos 2 opcoes validas.';
                    }
                }

                if ((bool) Arr::get($question, 'is_repeatable', false) && !(int) Arr::get($question, 'repeat_limit', 0)) {
                    $errors[$questionPath . '.repeat_limit'] = 'Pergunta repetivel precisa de limite de repeticao.';
                }

                foreach (Arr::get($question, 'complementary_fields', []) as $fieldIndex => $field) {
                    $fieldPath = $questionPath . '.complementary_fields.' . $fieldIndex;
                    if (trim((string) Arr::get($field, 'name')) === '' || trim((string) Arr::get($field, 'label')) === '') {
                        $errors[$fieldPath . '.name'] = 'Campo complementar precisa de nome e rotulo.';
                    }
                }
            }
        }

        if (!empty($errors)) {
            $this->addError('sections', 'Existem perguntas com estrutura invalida. Revise os campos obrigatorios.');
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function makeEmptySection(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'id' => null,
            'name' => 'Secao ' . $sortOrder,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'questions' => [$this->makeEmptyQuestion(1)],
        ];
    }

    private function makeEmptyQuestion(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'id' => null,
            'code' => '',
            'prompt' => '',
            'help_text' => '',
            'technician_note_label' => 'Relato tecnico',
            'answer_type' => 'text',
            'sort_order' => $sortOrder,
            'is_required' => false,
            'is_repeatable' => false,
            'repeat_limit' => null,
            'requires_photo_evidence' => false,
            'is_active' => true,
            'options' => [$this->makeEmptyOption(1), $this->makeEmptyOption(2)],
            'complementary_fields' => [],
            'conditional_rules' => [],
            'consequences' => [],
        ];
    }

    private function makeEmptyOption(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'id' => null,
            'label' => '',
            'value' => '',
            'sort_order' => $sortOrder,
            'is_active' => true,
        ];
    }

    private function makeEmptyComplementaryField(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'id' => null,
            'name' => '',
            'label' => '',
            'field_type' => 'text',
            'sort_order' => $sortOrder,
            'is_required' => false,
            'is_active' => true,
            'configuration' => [],
        ];
    }

    private function makeEmptyRule(int $sortOrder): array
    {
        return [
            'temp_id' => (string) Str::uuid(),
            'expected_option_temp_id' => null,
            'operator' => 'equals',
            'expected_value' => null,
            'target_field_temp_id' => null,
            'effect' => 'require',
            'is_active' => true,
            'sort_order' => $sortOrder,
        ];
    }

    private function makeEmptyConsequence(): array
    {
        return [
            'expected_option_temp_id' => null,
            'match_operator' => 'equals',
            'match_value' => null,
            'severity' => 'medium',
            'description' => '',
            'technical_action_name' => '',
            'should_generate_budget' => true,
            'visible_to_technician' => true,
            'recommendation_text' => null,
            'is_active' => true,
        ];
    }

    private function normalizeNullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function shouldPersistOption(string $answerType, array $option): bool
    {
        if (!in_array($answerType, ['single_select', 'multi_select', 'yes_no', 'radio'], true)) {
            return false;
        }

        return trim((string) Arr::get($option, 'label')) !== '' && trim((string) Arr::get($option, 'value')) !== '';
    }

    private function resolveTechnicalActionId(mixed $name): ?string
    {
        $normalizedName = trim((string) ($name ?? ''));
        if ($normalizedName === '') {
            return null;
        }

        $slug = Str::slug($normalizedName);
        $action = AnalysisTechnicalAction::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $normalizedName,
                'description' => null,
                'is_active' => true,
            ]
        );

        if ($action->name !== $normalizedName) {
            $action->update(['name' => $normalizedName]);
        }

        return $action->id;
    }

    private function reindexSections(): void
    {
        $this->sections = collect($this->sections)
            ->values()
            ->map(function (array $section, int $sectionIndex): array {
                $section['sort_order'] = $sectionIndex + 1;
                $section['questions'] = collect(Arr::get($section, 'questions', []))
                    ->values()
                    ->map(function (array $question, int $questionIndex): array {
                        $question['sort_order'] = $questionIndex + 1;
                        return $question;
                    })
                    ->all();

                return $section;
            })
            ->all();
    }

    private function reindexQuestions(int $sectionIndex): void
    {
        $this->sections[$sectionIndex]['questions'] = collect($this->sections[$sectionIndex]['questions'])
            ->values()
            ->map(function (array $question, int $questionIndex): array {
                $question['sort_order'] = $questionIndex + 1;
                return $question;
            })
            ->all();
    }
}
