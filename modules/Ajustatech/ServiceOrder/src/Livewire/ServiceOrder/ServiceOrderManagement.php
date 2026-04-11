<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Rules\CpfValidator;
use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceOrderManagement extends Component
{
    public string $title = '';

    public string $mode = 'create';

    public ?string $serviceOrderId = null;

    public array $serviceOrderForm = [
        'customer_id' => '',
        'equipment_type_id' => '',
        'selected_document_id' => '',
        'equipment_brand' => '',
        'equipment_model' => '',
        'equipment_serial_number' => '',
    ];

    public array $dynamicFields = [];

    public array $serviceItems = [];

    public string $customerSearch = '';

    public bool $showCustomerModal = false;

    public string $customerModalTab = 'list';

    public bool $showCustomerCorrectionModal = false;

    public int $customerCorrectionComponentKey = 0;

    public ?string $customerCorrectionTargetId = null;

    public array $newCustomer = [
        'name' => '',
        'person' => 'F',
        'cpf_cnpj' => '',
        'email' => '',
        'cellphone' => '',
        'zip_code' => '',
        'address' => '',
        'number' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
        'status' => '1',
    ];

    public array $customerCorrection = [
        'id' => '',
        'name' => '',
        'person' => 'F',
        'cpf_cnpj' => '',
        'email' => '',
        'cellphone' => '',
        'zip_code' => '',
        'address' => '',
        'number' => '',
        'neighborhood' => '',
        'city' => '',
        'state' => '',
    ];

    public bool $showDiscountModal = false;

    public ?int $discountItemIndex = null;

    public string $discountInput = '0.00';

    public bool $showEquipmentTypeModal = false;

    public string $equipmentTypeDraftId = '';

    public function mount(ServiceOrderServiceInterface $service, ?ServiceOrder $serviceOrder = null): void
    {
        if ($serviceOrder && $serviceOrder->exists) {
            $this->mode = request()->routeIs('service-order-list') ? 'view' : 'edit';
            $this->serviceOrderId = $serviceOrder->id;
            $loaded = $service->findServiceOrder($serviceOrder->id);

            $this->serviceOrderForm = [
                'customer_id' => (string) $loaded->customer_id,
                'equipment_type_id' => (string) ($loaded->equipment_type_id ?? ''),
                'selected_document_id' => (string) ($loaded->selected_document_id ?? ''),
                'equipment_brand' => (string) ($loaded->equipment_brand ?? ''),
                'equipment_model' => (string) ($loaded->equipment_model ?? ''),
                'equipment_serial_number' => (string) ($loaded->equipment_serial_number ?? ''),
            ];

            $this->serviceItems = $loaded->serviceItems
                ->map(fn ($serviceItem) => [
                    'procedure_id' => (string) ($serviceItem->procedure_id ?? ''),
                    'item_name' => (string) $serviceItem->item_name,
                    'item_notes' => (string) ($serviceItem->item_notes ?? ''),
                    'unit_value' => number_format((float) $serviceItem->unit_value, 2, '.', ''),
                    'discount_value' => number_format((float) $serviceItem->discount_value, 2, '.', ''),
                    'total_value' => number_format((float) $serviceItem->total_value, 2, '.', ''),
                ])
                ->values()
                ->all();

            $this->dynamicFields = $loaded->fieldValues
                ->map(fn ($fieldValue) => [
                    'equipment_type_field_id' => $fieldValue->equipment_type_field_id,
                    'field_type' => $fieldValue->field_type,
                    'field_label' => $fieldValue->field_label,
                    'field_placeholder' => $fieldValue->field_placeholder,
                    'is_required' => (bool) $fieldValue->is_required,
                    'value_text' => (string) ($fieldValue->value_text ?? ''),
                ])
                ->values()
                ->all();

            $this->title = $this->mode === 'view'
                ? trans('service-order::messages.service_order_view_title')
                : trans('service-order::messages.service_order_edit_title');

            $this->fillCustomerCorrectionFromCurrent();

            return;
        }

        $this->mode = 'create';
        $this->title = trans('service-order::messages.service_order_create_title');

        $customerId = (string) request()->query('customer_id', '');
        $equipmentTypeId = (string) request()->query('equipment_type_id', '');

        if ($customerId !== '') {
            $this->serviceOrderForm['customer_id'] = $customerId;
            $this->fillCustomerCorrectionFromCurrent();
        }

        if ($equipmentTypeId !== '') {
            $this->serviceOrderForm['equipment_type_id'] = $equipmentTypeId;
            $this->refreshEquipmentTypeContext($service, $equipmentTypeId);
        }

        $this->addServiceItem();
    }

    public function updatedServiceOrderForm($value, string $key): void
    {
        if ($key !== 'equipment_type_id') {
            return;
        }

        $this->refreshEquipmentTypeContext(app(ServiceOrderServiceInterface::class), (string) $this->serviceOrderForm['equipment_type_id']);
    }

    public function openCustomerModal(string $tab = 'list'): void
    {
        if ($this->mode !== 'create') {
            return;
        }

        $this->customerModalTab = in_array($tab, ['list', 'create'], true) ? $tab : 'list';
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
        $this->customerModalTab = 'list';
        $this->resetValidation(['newCustomer.*']);
    }

    public function selectCustomer(string $id): void
    {
        if ($this->mode !== 'create') {
            return;
        }

        $this->serviceOrderForm['customer_id'] = $id;
        $this->fillCustomerCorrectionFromCurrent();
        $this->closeCustomerModal();
    }

    public function createCustomerFromModal(): void
    {
        if ($this->mode !== 'create') {
            return;
        }

        $person = (string) ($this->newCustomer['person'] ?? 'F');

        $validated = $this->validate([
            'newCustomer.name' => ['required', 'string', 'min:3', 'max:255'],
            'newCustomer.person' => ['required', Rule::in(['F', 'J'])],
            'newCustomer.cpf_cnpj' => $person === 'F'
                ? ['required', Rule::unique('customers', 'cpf_cnpj'), new CpfValidator]
                : ['required', Rule::unique('customers', 'cpf_cnpj'), new CnpjValidation],
            'newCustomer.email' => ['required', 'email', Rule::unique('customers', 'email')],
            'newCustomer.zip_code' => ['required', 'string', 'min:8', 'max:20'],
            'newCustomer.address' => ['required', 'string', 'min:3', 'max:255'],
            'newCustomer.number' => ['required', 'string', 'max:30'],
            'newCustomer.neighborhood' => ['required', 'string', 'min:2', 'max:120'],
            'newCustomer.city' => ['required', 'string', 'min:2', 'max:120'],
            'newCustomer.state' => ['required', 'string', 'size:2'],
            'newCustomer.cellphone' => ['nullable', 'string', 'max:20'],
        ])['newCustomer'];

        $customer = Customer::query()->create($validated);

        $this->serviceOrderForm['customer_id'] = $customer->id;
        $this->fillCustomerCorrectionFromCurrent();

        $this->newCustomer = [
            'name' => '',
            'person' => 'F',
            'cpf_cnpj' => '',
            'email' => '',
            'cellphone' => '',
            'zip_code' => '',
            'address' => '',
            'number' => '',
            'neighborhood' => '',
            'city' => '',
            'state' => '',
            'status' => '1',
        ];

        $this->closeCustomerModal();
    }

    public function openCustomerCorrectionModal(): void
    {
        $this->prepareCustomerCorrectionModal();
    }

    public function prepareCustomerCorrectionModal(): void
    {
        if ($this->serviceOrderForm['customer_id'] === '') {
            return;
        }

        $this->customerCorrectionTargetId = (string) $this->serviceOrderForm['customer_id'];
        $this->customerCorrectionComponentKey++;
        $this->showCustomerCorrectionModal = true;
    }

    public function closeCustomerCorrectionModal(): void
    {
        $this->showCustomerCorrectionModal = false;
        $this->customerCorrectionTargetId = null;
    }

    #[On('customer-created')]
    public function handleCustomerCorrectionSaved(string $id = ''): void
    {
        if ($this->serviceOrderForm['customer_id'] === '') {
            return;
        }

        $targetId = $this->customerCorrectionTargetId ?: (string) $this->serviceOrderForm['customer_id'];

        if (blank($id) || $targetId !== $id) {
            return;
        }

        if ($this->serviceOrderId !== null) {
            app(ServiceOrderServiceInterface::class)->refreshServiceOrderCustomerSnapshot($this->serviceOrderId);
        }

        $this->customerCorrectionComponentKey++;
        $this->closeCustomerCorrectionModal();
        $this->dispatch('customer-correction-saved');
    }

    public function addServiceItem(): void
    {
        $this->serviceItems[] = [
            'procedure_id' => '',
            'item_name' => '',
            'item_notes' => '',
            'unit_value' => '0.00',
            'discount_value' => '0.00',
            'total_value' => '0.00',
        ];
    }

    public function removeServiceItem(int $index): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        unset($this->serviceItems[$index]);
        $this->serviceItems = array_values($this->serviceItems);
    }

    public function applyProcedureToItem(int $index, string $procedureId, ServiceOrderServiceInterface $service): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        $procedure = $service->listProcedures()->firstWhere('id', $procedureId);

        if (! $procedure) {
            return;
        }

        $this->serviceItems[$index]['procedure_id'] = $procedureId;
        $this->serviceItems[$index]['item_name'] = $procedure->name;
        $this->serviceItems[$index]['unit_value'] = number_format((float) $procedure->value, 2, '.', '');
        $this->recalculateItemTotal($index);
    }

    public function openDiscountModal(int $index): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        $this->discountItemIndex = $index;
        $this->discountInput = (string) ($this->serviceItems[$index]['discount_value'] ?? '0.00');
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
        $this->discountItemIndex = null;
        $this->discountInput = '0.00';
    }

    public function openEquipmentTypeModal(): void
    {
        if ($this->mode === 'view') {
            return;
        }

        $this->equipmentTypeDraftId = (string) $this->serviceOrderForm['equipment_type_id'];
        $this->showEquipmentTypeModal = true;
    }

    public function closeEquipmentTypeModal(): void
    {
        $this->showEquipmentTypeModal = false;
        $this->equipmentTypeDraftId = '';
    }

    public function saveEquipmentType(ServiceOrderServiceInterface $service): void
    {
        if ($this->mode === 'view') {
            return;
        }

        $validated = $this->validate([
            'equipmentTypeDraftId' => ['required', 'uuid', 'exists:service_order_equipment_types,id'],
        ], [], [
            'equipmentTypeDraftId' => trans('service-order::messages.equipment_type'),
        ]);

        $this->serviceOrderForm['equipment_type_id'] = $validated['equipmentTypeDraftId'];
        $this->serviceOrderForm['selected_document_id'] = '';
        $this->refreshEquipmentTypeContext($service, $validated['equipmentTypeDraftId']);
        $this->closeEquipmentTypeModal();
    }

    public function applyDiscount(): void
    {
        if ($this->discountItemIndex === null || ! isset($this->serviceItems[$this->discountItemIndex])) {
            return;
        }

        $discountValue = max(0, round((float) $this->discountInput, 2));
        $this->serviceItems[$this->discountItemIndex]['discount_value'] = number_format($discountValue, 2, '.', '');
        $this->recalculateItemTotal($this->discountItemIndex);
        $this->closeDiscountModal();
    }

    public function save(ServiceOrderServiceInterface $service): void
    {
        if ($this->mode === 'view') {
            return;
        }

        $validated = $this->validate($this->baseRules(), [], $this->attributeNames());

        $payload = [
            'customer_id' => $validated['serviceOrderForm']['customer_id'],
            'equipment_type_id' => $validated['serviceOrderForm']['equipment_type_id'] ?: null,
            'selected_document_id' => $validated['serviceOrderForm']['selected_document_id'] ?: null,
            'equipment_brand' => trim((string) ($validated['serviceOrderForm']['equipment_brand'] ?? '')),
            'equipment_model' => trim((string) ($validated['serviceOrderForm']['equipment_model'] ?? '')),
            'equipment_serial_number' => trim((string) ($validated['serviceOrderForm']['equipment_serial_number'] ?? '')),
            'dynamic_fields' => collect($this->dynamicFields)->map(function (array $field) {
                return [
                    'equipment_type_field_id' => $field['equipment_type_field_id'] ?? null,
                    'field_type' => $field['field_type'] ?? 'text',
                    'field_label' => trim((string) ($field['field_label'] ?? '')),
                    'field_placeholder' => $field['field_placeholder'] ?? null,
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'value_text' => trim((string) ($field['value_text'] ?? '')),
                ];
            })->all(),
            'service_items' => collect($this->serviceItems)->map(function (array $item) {
                $unitValue = max(0, round((float) ($item['unit_value'] ?? 0), 2));
                $discountValue = max(0, round((float) ($item['discount_value'] ?? 0), 2));

                return [
                    'procedure_id' => $item['procedure_id'] ?: null,
                    'item_name' => trim((string) ($item['item_name'] ?? '')),
                    'item_notes' => trim((string) ($item['item_notes'] ?? '')),
                    'unit_value' => $unitValue,
                    'discount_value' => min($discountValue, $unitValue),
                ];
            })->all(),
        ];

        if ($this->mode === 'create') {
            $created = $service->createServiceOrder($payload);
            $this->redirectRoute('service-order-edit', ['serviceOrder' => $created->id]);

            return;
        }

        if ($this->serviceOrderId !== null) {
            $service->updateServiceOrder($this->serviceOrderId, $payload);
        }
    }

    public function render(ServiceOrderServiceInterface $service)
    {
        $selectedCustomer = $this->serviceOrderForm['customer_id'] !== ''
            ? Customer::query()->find($this->serviceOrderForm['customer_id'])
            : null;

        $currentServiceOrder = $this->serviceOrderId ? $service->findServiceOrder($this->serviceOrderId) : null;

        $equipmentTypes = $service->listActiveEquipmentTypes();

        $selectedEquipmentType = $equipmentTypes->firstWhere('id', (string) $this->serviceOrderForm['equipment_type_id']);

        $documents = collect($selectedEquipmentType?->documents ?? [])
            ->values()
            ->map(function (ServiceOrderEquipmentTypeDocument $document) use ($selectedCustomer, $currentServiceOrder): array {
                $previewText = null;

                if ($document->document_type === ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE && filled($document->template_content)) {
                    $previewText = str_replace(
                        [
                            '{{dados_cliente}}',
                            '{{equipamento_modelo}}',
                            '{{numero_ordem_servico}}',
                        ],
                        [
                            (string) ($selectedCustomer?->name ?? ''),
                            (string) ($this->serviceOrderForm['equipment_model'] ?? ''),
                            (string) ($currentServiceOrder?->order_number ?? ''),
                        ],
                        (string) $document->template_content
                    );
                }

                return [
                    'id' => $document->id,
                    'title' => (string) ($document->title ?: trans('service-order::messages.equipment_type_document_without_title')),
                    'document_type' => $document->document_type,
                    'document_type_label' => $document->document_type === ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE
                        ? trans('service-order::messages.equipment_type_template_label')
                        : 'PDF',
                    'icon' => $document->document_type === ServiceOrderEquipmentTypeDocument::TYPE_EDITABLE_TEMPLATE
                        ? 'ti-file-description'
                        : 'ti-file-type-pdf',
                    'preview_url' => $document->document_type === ServiceOrderEquipmentTypeDocument::TYPE_FIXED_PDF && filled($document->path)
                        ? route('service-order-equipment-types-document-file', ['id' => $document->id])
                        : null,
                    'preview_text' => $previewText,
                    'is_selected' => (string) $this->serviceOrderForm['selected_document_id'] === $document->id,
                ];
            })
            ->all();

        return view('service-order::livewire.service-order.service-order-management', [
            'selectedCustomer' => $selectedCustomer,
            'currentServiceOrder' => $currentServiceOrder,
            'customerForCorrectionModal' => $this->customerCorrectionTargetId
                ? Customer::query()->find($this->customerCorrectionTargetId)
                : $selectedCustomer,
            'availableCustomers' => $service->searchCustomers($this->customerSearch, 20),
            'equipmentTypes' => $equipmentTypes,
            'selectedEquipmentType' => $selectedEquipmentType,
            'equipmentDocuments' => $documents,
            'procedures' => $service->listProcedures(),
            'statusFlows' => $service->listStatusFlows(),
            'documentPreviewTitle' => trans('service-order::messages.equipment_type_document_preview_title'),
            'documentPreviewPendingFile' => trans('service-order::messages.equipment_type_pending_file_preview'),
        ]);
    }

    private function recalculateItemTotal(int $index): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        $unitValue = max(0, round((float) ($this->serviceItems[$index]['unit_value'] ?? 0), 2));
        $discountValue = max(0, round((float) ($this->serviceItems[$index]['discount_value'] ?? 0), 2));
        $discountValue = min($discountValue, $unitValue);
        $totalValue = max(0, $unitValue - $discountValue);

        $this->serviceItems[$index]['unit_value'] = number_format($unitValue, 2, '.', '');
        $this->serviceItems[$index]['discount_value'] = number_format($discountValue, 2, '.', '');
        $this->serviceItems[$index]['total_value'] = number_format($totalValue, 2, '.', '');
    }

    private function refreshEquipmentTypeContext(ServiceOrderServiceInterface $service, string $equipmentTypeId): void
    {
        $equipmentType = $service->listActiveEquipmentTypes()
            ->firstWhere('id', $equipmentTypeId);

        $this->dynamicFields = collect($equipmentType?->fields ?? [])
            ->map(fn ($field) => [
                'equipment_type_field_id' => $field->id,
                'field_type' => $field->field_type,
                'field_label' => $field->label,
                'field_placeholder' => $field->placeholder,
                'is_required' => (bool) $field->is_required,
                'value_text' => (string) ($field->default_text ?? ''),
            ])
            ->values()
            ->all();
    }

    private function fillCustomerCorrectionFromCurrent(): void
    {
        if ($this->serviceOrderForm['customer_id'] === '') {
            return;
        }

        $customer = Customer::query()->find($this->serviceOrderForm['customer_id']);

        if (! $customer) {
            return;
        }

        $this->customerCorrection = [
            'id' => $customer->id,
            'name' => (string) $customer->name,
            'person' => (string) $customer->person,
            'cpf_cnpj' => (string) $customer->cpf_cnpj,
            'email' => (string) $customer->email,
            'cellphone' => (string) ($customer->cellphone ?? ''),
            'zip_code' => (string) $customer->zip_code,
            'address' => (string) $customer->address,
            'number' => (string) $customer->number,
            'neighborhood' => (string) $customer->neighborhood,
            'city' => (string) $customer->city,
            'state' => (string) $customer->state,
        ];
    }

    private function baseRules(): array
    {
        return [
            'serviceOrderForm.customer_id' => ['required', 'uuid', 'exists:customers,id'],
            'serviceOrderForm.equipment_type_id' => ['required', 'uuid', 'exists:service_order_equipment_types,id'],
            'serviceOrderForm.selected_document_id' => ['nullable', 'uuid', 'exists:service_order_equipment_type_documents,id'],
            'serviceOrderForm.equipment_brand' => ['nullable', 'string', 'max:120'],
            'serviceOrderForm.equipment_model' => ['required', 'string', 'max:120'],
            'serviceOrderForm.equipment_serial_number' => ['nullable', 'string', 'max:120'],
            'dynamicFields.*.value_text' => ['nullable', 'string', 'max:1000'],
            'serviceItems.*.item_name' => ['nullable', 'string', 'max:150'],
            'serviceItems.*.item_notes' => ['nullable', 'string', 'max:1000'],
            'serviceItems.*.unit_value' => ['nullable', 'numeric', 'min:0'],
            'serviceItems.*.discount_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function attributeNames(): array
    {
        return [
            'serviceOrderForm.customer_id' => trans('service-order::messages.customer'),
            'serviceOrderForm.equipment_type_id' => trans('service-order::messages.equipment_type'),
            'serviceOrderForm.equipment_model' => trans('service-order::messages.equipment_model'),
            'serviceOrderForm.equipment_brand' => trans('service-order::messages.equipment_brand'),
            'serviceOrderForm.equipment_serial_number' => trans('service-order::messages.equipment_serial_number'),
        ];
    }
}
