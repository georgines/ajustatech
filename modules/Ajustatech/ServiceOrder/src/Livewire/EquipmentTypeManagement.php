<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Services\EquipmentTypeFormService;
use Ajustatech\ServiceOrder\Services\EquipmentTypeImageStorageService;
use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class EquipmentTypeManagement extends Component
{
    use WithFileUploads;

    public string $title = 'Cadastro de Tipo de Equipamento';
    public string $mode = 'create';
    public ?string $equipmentTypeId = null;

    public string $name = '';
    public ?string $description = null;
    public bool $is_active = true;
    public mixed $image = null;
    public bool $removeImage = false;
    public ?string $currentImageUrl = null;
    public string $imageAccept = '.jpg,.jpeg,.png,.webp';

    public array $fields = [];
    public array $fieldTypes = [];
    public array $fieldTypeLabels = [];

    public function mount(EquipmentTypeImageStorageService $imageStorageService, EquipmentTypeFormService $formService, ?string $id = null): void
    {
        $this->imageAccept = $imageStorageService->acceptAttribute();
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
            $this->fields = $formService->hydrateFieldsForEdit($equipmentType);
            $this->currentImageUrl = $equipmentType->image_url;

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
        $this->fields[$index]['configuration'] = app(EquipmentTypeFormService::class)->defaultConfigurationForType($type);
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

    public function updatedImage(): void
    {
        if ($this->image) {
            $this->removeImage = false;
        }
    }

    public function updatedRemoveImage(bool $value): void
    {
        if ($value) {
            $this->image = null;
        }
    }

    public function save(EquipmentTypeService $service, EquipmentTypeFormService $formService)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'removeImage' => 'boolean',
            'fields' => 'array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.field_type' => 'required|string',
        ]);

        $payload = [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'fields' => $formService->normalizeFields($this->fields),
        ];

        try {
            if ($this->mode === 'edit' && $this->equipmentTypeId) {
                $service->update($this->equipmentTypeId, $payload, $this->image, $this->removeImage);
            } else {
                $service->create($payload, $this->image);
            }
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->getMessageBag());
            return null;
        }

        return redirect()->route('service-order-equipment-types-show');
    }

    private function defaultConfigurationForType(string $type): array
    {
        return app(EquipmentTypeFormService::class)->defaultConfigurationForType($type);
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
