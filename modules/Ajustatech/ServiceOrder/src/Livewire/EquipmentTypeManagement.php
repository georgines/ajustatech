<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class EquipmentTypeManagement extends Component
{
    public string $title = 'Cadastro de Tipo de Equipamento';
    public string $mode = 'create';
    public ?string $equipmentTypeId = null;

    public string $name = '';
    public ?string $description = null;
    public bool $is_active = true;

    public array $fields = [];
    public array $fieldTypes = [];
    public array $fieldTypeLabels = [];

    public function mount(?string $id = null): void
    {
        $this->fieldTypes = EquipmentFieldType::values();
        $this->fieldTypeLabels = [
            EquipmentFieldType::PHOTO => 'Foto',
            EquipmentFieldType::TEXT => 'Texto',
            EquipmentFieldType::SELECT => 'Seletor',
            EquipmentFieldType::RADIO => 'Radio',
            EquipmentFieldType::FILE => 'Arquivo',
            EquipmentFieldType::DOCUMENT => 'Documento',
        ];

        if ($id) {
            $this->mode = 'edit';
            $this->equipmentTypeId = $id;
            $this->title = 'Editar Tipo de Equipamento';

            $equipmentType = EquipmentType::findWithFieldsAndOptionsOrFail($id);
            $this->name = $equipmentType->name;
            $this->description = $equipmentType->description;
            $this->is_active = (bool) $equipmentType->is_active;
            $this->fields = $equipmentType->fields
                ->sortBy('sort_order')
                ->values()
                ->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'field_type' => $field->field_type,
                        'name' => $field->name,
                        'slug' => $field->slug,
                        'sort_order' => $field->sort_order,
                        'is_required' => (bool) $field->is_required,
                        'is_printable' => (bool) $field->is_printable,
                        'is_active' => (bool) $field->is_active,
                        'configuration' => $field->configuration ?? [],
                        'options' => $field->options
                            ->sortBy('sort_order')
                            ->values()
                            ->map(fn ($option) => [
                                'label' => $option->label,
                                'value' => $option->value,
                                'sort_order' => (int) $option->sort_order,
                                'is_active' => (bool) $option->is_active,
                            ])
                            ->all(),
                    ];
                })
                ->all();

            return;
        }

        $this->addField('text');
    }

    public function addField(string $type = 'text'): void
    {
        $index = count($this->fields);

        $this->fields[] = [
            'field_type' => $type,
            'name' => '',
            'slug' => '',
            'sort_order' => $index + 1,
            'is_required' => false,
            'is_printable' => !in_array($type, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true),
            'is_active' => true,
            'configuration' => $this->defaultConfigurationForType($type),
            'options' => EquipmentFieldType::acceptsOptions($type)
                ? [
                    ['label' => 'Opcao 1', 'value' => 'opcao_1', 'sort_order' => 1, 'is_active' => true],
                ]
                : [],
        ];
    }

    public function removeField(int $index): void
    {
        if (!array_key_exists($index, $this->fields)) {
            return;
        }

        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
        $this->reindexSortOrder();
    }

    public function moveFieldUp(int $index): void
    {
        if ($index <= 0 || !array_key_exists($index, $this->fields)) {
            return;
        }

        $previousIndex = $index - 1;
        [$this->fields[$previousIndex], $this->fields[$index]] = [$this->fields[$index], $this->fields[$previousIndex]];
        $this->reindexSortOrder();
    }

    public function moveFieldDown(int $index): void
    {
        if (!array_key_exists($index, $this->fields)) {
            return;
        }

        $nextIndex = $index + 1;
        if (!array_key_exists($nextIndex, $this->fields)) {
            return;
        }

        [$this->fields[$index], $this->fields[$nextIndex]] = [$this->fields[$nextIndex], $this->fields[$index]];
        $this->reindexSortOrder();
    }

    public function updatedFields($value, string $name): void
    {
        if (!str_ends_with($name, '.field_type')) {
            return;
        }

        $parts = explode('.', $name);
        $index = (int) Arr::get($parts, 0, 0);

        if (!array_key_exists($index, $this->fields)) {
            return;
        }

        $type = $this->fields[$index]['field_type'];
        $this->fields[$index]['configuration'] = $this->defaultConfigurationForType($type);
        $this->fields[$index]['is_printable'] = !in_array($type, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true);
        $this->fields[$index]['options'] = EquipmentFieldType::acceptsOptions($type)
            ? [['label' => 'Opcao 1', 'value' => 'opcao_1', 'sort_order' => 1, 'is_active' => true]]
            : [];
    }

    public function addOption(int $fieldIndex): void
    {
        if (!array_key_exists($fieldIndex, $this->fields)) {
            return;
        }

        $options = $this->fields[$fieldIndex]['options'] ?? [];
        $next = count($options) + 1;
        $options[] = [
            'label' => 'Opcao ' . $next,
            'value' => 'opcao_' . $next,
            'sort_order' => $next,
            'is_active' => true,
        ];
        $this->fields[$fieldIndex]['options'] = $options;
    }

    public function removeOption(int $fieldIndex, int $optionIndex): void
    {
        if (!isset($this->fields[$fieldIndex]['options'][$optionIndex])) {
            return;
        }

        unset($this->fields[$fieldIndex]['options'][$optionIndex]);
        $this->fields[$fieldIndex]['options'] = array_values($this->fields[$fieldIndex]['options']);
        foreach ($this->fields[$fieldIndex]['options'] as $index => &$option) {
            $option['sort_order'] = $index + 1;
        }
    }

    public function save(EquipmentTypeService $service)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'fields' => 'array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
        ]);

        $payload = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'fields' => $this->normalizedFields(),
        ];

        try {
            if ($this->mode === 'edit' && $this->equipmentTypeId) {
                $service->update($this->equipmentTypeId, $payload);
            } else {
                $service->create($payload);
            }
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->getMessageBag());
            return null;
        }

        return redirect()->route('service-order-equipment-types-show');
    }

    private function normalizedFields(): array
    {
        $normalized = [];

        foreach ($this->fields as $index => $field) {
            $type = (string) Arr::get($field, 'field_type', EquipmentFieldType::TEXT);
            $name = trim((string) Arr::get($field, 'name', ''));
            $slugInput = trim((string) Arr::get($field, 'slug', ''));

            $item = [
                'id' => Arr::get($field, 'id'),
                'field_type' => $type,
                'name' => $name,
                'slug' => $slugInput !== '' ? Str::slug($slugInput, '_') : Str::slug($name, '_'),
                'sort_order' => $index + 1,
                'is_required' => (bool) Arr::get($field, 'is_required', false),
                'is_printable' => in_array($type, [EquipmentFieldType::PHOTO, EquipmentFieldType::FILE], true)
                    ? false
                    : (bool) Arr::get($field, 'is_printable', false),
                'is_active' => (bool) Arr::get($field, 'is_active', true),
                'configuration' => Arr::get($field, 'configuration', []),
                'options' => [],
            ];

            if (EquipmentFieldType::acceptsOptions($type)) {
                $item['options'] = collect(Arr::get($field, 'options', []))
                    ->values()
                    ->map(function (array $option, int $optionIndex) {
                        $label = trim((string) Arr::get($option, 'label', ''));
                        $value = trim((string) Arr::get($option, 'value', ''));

                        return [
                            'label' => $label,
                            'value' => $value !== '' ? Str::slug($value, '_') : Str::slug($label, '_'),
                            'sort_order' => $optionIndex + 1,
                            'is_active' => (bool) Arr::get($option, 'is_active', true),
                        ];
                    })
                    ->all();
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    private function defaultConfigurationForType(string $type): array
    {
        return match ($type) {
            EquipmentFieldType::PHOTO => [
                'max_files' => 1,
                'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            ],
            EquipmentFieldType::TEXT => [
                'placeholder' => '',
                'help' => '',
                'max_length' => 500,
            ],
            EquipmentFieldType::FILE => [
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'preview_mode' => 'modal',
            ],
            EquipmentFieldType::DOCUMENT => [
                'template' => 'Cliente: {{cliente_nome}}',
                'help' => 'Use variaveis disponiveis para montagem do documento final.',
            ],
            default => [],
        };
    }

    private function reindexSortOrder(): void
    {
        foreach ($this->fields as $index => &$field) {
            $field['sort_order'] = $index + 1;
        }
    }

    public function render()
    {
        return view('service-order::livewire.equipment-type-management');
    }
}
