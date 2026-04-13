<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\Core\Rules\CnpjValidation;
use Ajustatech\Core\Rules\CpfValidator;
use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder\ServiceOrder;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCatalogServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCustomerServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderEquipmentCatalogServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderRecordServiceInterface;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceOrderManagement extends Component
{
    use SwitchAlertDispatch;

    public string $title = '';

    #[Locked]
    public string $mode = 'create';

    #[Locked]
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

    public array $currentServiceOrder = [];

    public array $selectedCustomer = [];

    public array $availableCustomers = [];

    public string $customerSearch = '';

    public bool $showCustomerModal = false;

    public string $customerModalTab = 'list';

    public bool $showCustomerCorrectionModal = false;

    public int $customerCorrectionComponentKey = 0;

    #[Locked]
    public ?string $customerCorrectionTargetId = null;

    #[Locked]
    public ?Customer $customerForCorrectionModal = null;

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

    public array $equipmentTypes = [];

    public array $selectedEquipmentType = [];

    public bool $showEquipmentBrandModal = false;

    public string $equipmentBrandSearch = '';

    public bool $equipmentBrandSearchActive = false;

    public array $equipmentTypeBrands = [];

    #[Locked]
    public ?string $selectedEquipmentBrandId = null;

    public bool $showEquipmentModelModal = false;

    public string $equipmentModelSearch = '';

    public bool $equipmentModelSearchActive = false;

    public array $equipmentTypeModels = [];

    public array $analysisServices = [];

    public array $statusFlows = [];

    public function mount(?ServiceOrder $serviceOrder = null): void
    {
        $this->equipmentTypes = $this->equipmentCatalogService()->listActiveEquipmentTypes();
        $this->analysisServices = $this->catalogService()->listAnalysisServices();
        $this->statusFlows = $this->catalogService()->listStatusFlows();

        if ($serviceOrder && $serviceOrder->exists) {
            $this->mode = request()->routeIs('service-order-list') ? 'view' : 'edit';
            $this->serviceOrderId = $serviceOrder->id;
            $this->hydrateFromServiceOrder($this->recordService()->findServiceOrder($serviceOrder->id));
            $this->title = $this->mode === 'view'
                ? trans('service-order::messages.service_order_view_title')
                : trans('service-order::messages.service_order_edit_title');

            return;
        }

        $this->mode = 'create';
        $this->title = trans('service-order::messages.service_order_create_title');

        $customerId = (string) request()->query('customer_id', '');
        $equipmentTypeId = (string) request()->query('equipment_type_id', '');

        if ($customerId !== '') {
            $this->assignSelectedCustomer($customerId);
        }

        if ($equipmentTypeId !== '') {
            $this->applyEquipmentType($equipmentTypeId);
        }

        $this->addServiceItem();
    }

    public function updatedServiceOrderForm($value, string $key): void
    {
        if ($key !== 'equipment_type_id') {
            return;
        }

        $equipmentTypeId = trim((string) ($this->serviceOrderForm['equipment_type_id'] ?? ''));

        if ($equipmentTypeId === '') {
            $this->resetEquipmentContext();

            return;
        }

        $this->applyEquipmentType($equipmentTypeId);
    }

    public function updatedCustomerSearch(): void
    {
        $this->customerSearch = mb_substr(trim($this->customerSearch), 0, 120);
        $this->loadAvailableCustomers($this->customerSearch);
    }

    public function openCustomerModal(string $tab = 'list'): void
    {
        if ($this->mode !== 'create') {
            return;
        }

        $this->customerModalTab = in_array($tab, ['list', 'create'], true) ? $tab : 'list';
        $this->showCustomerModal = true;

        if ($this->customerModalTab === 'list') {
            $this->loadAvailableCustomers($this->customerSearch);
        }
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
        $this->customerModalTab = 'list';
        $this->customerSearch = '';
        $this->availableCustomers = [];
        $this->resetValidation(['newCustomer.*']);
    }

    public function selectCustomer(string $id): void
    {
        if ($this->mode !== 'create') {
            return;
        }

        $this->assignSelectedCustomer($id);
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

        $created = $this->customerService()->createCustomer($this->sanitizeCustomerPayload($validated));
        $this->assignSelectedCustomer((string) $created['id']);
        $this->resetNewCustomerForm();
        $this->closeCustomerModal();
    }

    public function openCustomerCorrectionModal(): void
    {
        $this->prepareCustomerCorrectionModal();
    }

    public function prepareCustomerCorrectionModal(): void
    {
        if (($this->serviceOrderForm['customer_id'] ?? '') === '') {
            return;
        }

        $this->customerCorrectionTargetId = (string) $this->serviceOrderForm['customer_id'];
        $this->customerForCorrectionModal = $this->customerService()->findCustomerForEditingOrFail($this->customerCorrectionTargetId);
        $this->customerCorrectionComponentKey++;
        $this->showCustomerCorrectionModal = true;
    }

    public function closeCustomerCorrectionModal(): void
    {
        $this->showCustomerCorrectionModal = false;
        $this->customerCorrectionTargetId = null;
        $this->customerForCorrectionModal = null;
    }

    #[On('customer-created')]
    public function handleCustomerCorrectionSaved(string $id = ''): void
    {
        if (($this->serviceOrderForm['customer_id'] ?? '') === '') {
            return;
        }

        $targetId = $this->customerCorrectionTargetId ?: (string) $this->serviceOrderForm['customer_id'];

        if (blank($id) || $targetId !== $id) {
            return;
        }

        if ($this->serviceOrderId !== null) {
            $refreshed = $this->recordService()->refreshServiceOrderCustomerSnapshot($this->serviceOrderId);
            $this->currentServiceOrder = $refreshed->toManagementMeta();
        }

        $this->assignSelectedCustomer((string) $this->serviceOrderForm['customer_id']);
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

        $this->dispatchConfirmation(trans('service-order::messages.service_item_confirm_delete'))
            ->typeWarning()
            ->setButtonOK(trans('service-order::messages.confirm_yes'))
            ->setButtonCancel(trans('service-order::messages.confirm_no'))
            ->to('service-order-remove-item', index: $index)
            ->run();
    }

    #[On('service-order-remove-item')]
    public function removeServiceItemConfirmed(int $index): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        unset($this->serviceItems[$index]);
        $this->serviceItems = array_values($this->serviceItems);
    }

    public function applyAnalysisServiceToItem(int $index, string $analysisServiceId): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        $analysisService = collect($this->analysisServices)->firstWhere('id', $analysisServiceId);

        if (! is_array($analysisService)) {
            return;
        }

        $this->serviceItems[$index]['procedure_id'] = (string) $analysisService['id'];
        $this->serviceItems[$index]['item_name'] = (string) $analysisService['name'];
        $this->serviceItems[$index]['unit_value'] = number_format((float) ($analysisService['value'] ?? 0), 2, '.', '');
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

        $this->equipmentTypeDraftId = (string) ($this->serviceOrderForm['equipment_type_id'] ?? '');
        $this->showEquipmentTypeModal = true;
    }

    public function closeEquipmentTypeModal(): void
    {
        $this->showEquipmentTypeModal = false;
        $this->equipmentTypeDraftId = '';
    }

    public function saveEquipmentType(): void
    {
        if ($this->mode === 'view') {
            return;
        }

        $validated = $this->validate([
            'equipmentTypeDraftId' => ['required', 'uuid', 'exists:service_order_equipment_types,id'],
        ], [], [
            'equipmentTypeDraftId' => trans('service-order::messages.equipment_type'),
        ]);

        $this->applyEquipmentType($validated['equipmentTypeDraftId']);
        $this->closeEquipmentTypeModal();
    }

    public function openEquipmentBrandModal(): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        $this->selectedEquipmentBrandId = $this->equipmentCatalogService()->resolveBrandId(
            (string) $this->serviceOrderForm['equipment_type_id'],
            (string) ($this->serviceOrderForm['equipment_brand'] ?? '')
        );
        $this->equipmentBrandSearch = '';
        $this->equipmentBrandSearchActive = false;
        $this->equipmentTypeBrands = [];
        $this->showEquipmentBrandModal = true;
    }

    public function closeEquipmentBrandModal(): void
    {
        $this->showEquipmentBrandModal = false;
        $this->equipmentBrandSearch = '';
        $this->equipmentBrandSearchActive = false;
        $this->equipmentTypeBrands = [];
    }

    public function updatedEquipmentBrandSearch(string $value): void
    {
        $this->equipmentBrandSearch = mb_substr(trim($value), 0, 120);
        $this->equipmentBrandSearchActive = filled($this->equipmentBrandSearch);
        $this->equipmentTypeBrands = $this->equipmentBrandSearchActive && ($this->serviceOrderForm['equipment_type_id'] ?? '') !== ''
            ? $this->equipmentCatalogService()->searchBrands((string) $this->serviceOrderForm['equipment_type_id'], $this->equipmentBrandSearch)
            : [];
    }

    public function saveEquipmentBrand(): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        $validated = $this->validate([
            'equipmentBrandSearch' => ['required', 'string', 'max:120'],
        ], [], [
            'equipmentBrandSearch' => trans('service-order::messages.equipment_brand'),
        ]);

        $brand = $this->equipmentCatalogService()->rememberBrand(
            (string) $this->serviceOrderForm['equipment_type_id'],
            $validated['equipmentBrandSearch']
        );

        $this->applyBrandSelection($brand);
        $this->closeEquipmentBrandModal();
    }

    public function selectEquipmentBrand(string $brandId): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        $brand = $this->equipmentCatalogService()->selectBrand((string) $this->serviceOrderForm['equipment_type_id'], $brandId);

        if (! is_array($brand)) {
            return;
        }

        $rememberedBrand = $this->equipmentCatalogService()->rememberBrand(
            (string) $this->serviceOrderForm['equipment_type_id'],
            (string) $brand['name']
        );

        $this->applyBrandSelection($rememberedBrand);
        $this->closeEquipmentBrandModal();
    }

    public function openEquipmentModelModal(): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        if ($this->selectedEquipmentBrandId === null) {
            $this->selectedEquipmentBrandId = $this->equipmentCatalogService()->resolveBrandId(
                (string) $this->serviceOrderForm['equipment_type_id'],
                (string) ($this->serviceOrderForm['equipment_brand'] ?? '')
            );
        }

        $this->equipmentModelSearch = '';
        $this->equipmentModelSearchActive = false;
        $this->equipmentTypeModels = [];
        $this->showEquipmentModelModal = true;
    }

    public function closeEquipmentModelModal(): void
    {
        $this->showEquipmentModelModal = false;
        $this->equipmentModelSearch = '';
        $this->equipmentModelSearchActive = false;
        $this->equipmentTypeModels = [];
    }

    public function updatedEquipmentModelSearch(string $value): void
    {
        $this->equipmentModelSearch = mb_substr(trim($value), 0, 120);
        $this->equipmentModelSearchActive = filled($this->equipmentModelSearch);
        $this->equipmentTypeModels = $this->equipmentModelSearchActive && $this->selectedEquipmentBrandId
            ? $this->equipmentCatalogService()->searchModels($this->selectedEquipmentBrandId, $this->equipmentModelSearch)
            : [];
    }

    public function saveEquipmentModel(): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        if ($this->selectedEquipmentBrandId === null) {
            $this->selectedEquipmentBrandId = $this->equipmentCatalogService()->resolveBrandId(
                (string) $this->serviceOrderForm['equipment_type_id'],
                (string) ($this->serviceOrderForm['equipment_brand'] ?? '')
            );
        }

        if ($this->selectedEquipmentBrandId === null) {
            $this->addError('equipmentModelSearch', trans('service-order::messages.equipment_model_brand_required'));

            return;
        }

        $validated = $this->validate([
            'equipmentModelSearch' => ['required', 'string', 'max:120'],
        ], [], [
            'equipmentModelSearch' => trans('service-order::messages.equipment_model'),
        ]);

        $model = $this->equipmentCatalogService()->rememberModel($this->selectedEquipmentBrandId, $validated['equipmentModelSearch']);
        $this->serviceOrderForm['equipment_model'] = (string) $model['name'];
        $this->closeEquipmentModelModal();
    }

    public function selectEquipmentModel(string $modelId): void
    {
        if ($this->mode === 'view' || ($this->serviceOrderForm['equipment_type_id'] ?? '') === '') {
            return;
        }

        if ($this->selectedEquipmentBrandId === null) {
            $this->selectedEquipmentBrandId = $this->equipmentCatalogService()->resolveBrandId(
                (string) $this->serviceOrderForm['equipment_type_id'],
                (string) ($this->serviceOrderForm['equipment_brand'] ?? '')
            );
        }

        if ($this->selectedEquipmentBrandId === null) {
            return;
        }

        $model = $this->equipmentCatalogService()->selectModel($this->selectedEquipmentBrandId, $modelId);

        if (! is_array($model)) {
            return;
        }

        $rememberedModel = $this->equipmentCatalogService()->rememberModel($this->selectedEquipmentBrandId, (string) $model['name']);
        $this->serviceOrderForm['equipment_model'] = (string) $rememberedModel['name'];
        $this->closeEquipmentModelModal();
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

    public function save(): void
    {
        if ($this->mode === 'view') {
            return;
        }

        $validated = $this->validate($this->baseRules(), [], $this->attributeNames());

        $payload = [
            'customer_id' => $validated['serviceOrderForm']['customer_id'],
            'equipment_type_id' => blank($validated['serviceOrderForm']['equipment_type_id'] ?? null) ? null : $validated['serviceOrderForm']['equipment_type_id'],
            'selected_document_id' => blank($validated['serviceOrderForm']['selected_document_id'] ?? null) ? null : $validated['serviceOrderForm']['selected_document_id'],
            'equipment_brand' => trim((string) ($validated['serviceOrderForm']['equipment_brand'] ?? '')),
            'equipment_model' => trim((string) ($validated['serviceOrderForm']['equipment_model'] ?? '')),
            'equipment_serial_number' => trim((string) ($validated['serviceOrderForm']['equipment_serial_number'] ?? '')),
            'dynamic_fields' => collect($this->dynamicFields)->map(function (array $field) {
                return [
                    'equipment_type_field_id' => $field['equipment_type_field_id'] ?? null,
                    'field_type' => $field['field_type'] ?? 'text',
                    'field_label' => trim((string) ($field['field_label'] ?? '')),
                    'field_placeholder' => blank($field['field_placeholder'] ?? null) ? null : trim((string) $field['field_placeholder']),
                    'is_required' => (bool) ($field['is_required'] ?? false),
                    'value_text' => trim((string) ($field['value_text'] ?? '')),
                ];
            })->all(),
            'service_items' => collect($this->serviceItems)->map(function (array $item) {
                $unitValue = max(0, round((float) ($item['unit_value'] ?? 0), 2));
                $discountValue = min(max(0, round((float) ($item['discount_value'] ?? 0), 2)), $unitValue);

                return [
                    'procedure_id' => null,
                    'item_name' => trim((string) ($item['item_name'] ?? '')),
                    'item_notes' => trim((string) ($item['item_notes'] ?? '')),
                    'unit_value' => $unitValue,
                    'discount_value' => $discountValue,
                ];
            })->all(),
        ];

        if ($this->mode === 'create') {
            $created = $this->recordService()->createServiceOrder($payload);
            $this->redirectRoute('service-order-edit', ['serviceOrder' => $created->id]);

            return;
        }

        if ($this->serviceOrderId !== null) {
            $this->recordService()->updateServiceOrder($this->serviceOrderId, $payload);
            $this->redirectRoute('service-order-list', ['serviceOrder' => $this->serviceOrderId]);
        }
    }

    public function render()
    {
        return view('service-order::livewire.service-order.service-order-management', [
            'selectedCustomer' => $this->selectedCustomer,
            'currentServiceOrder' => $this->currentServiceOrder,
            'customerForCorrectionModal' => $this->customerForCorrectionModal,
            'availableCustomers' => $this->availableCustomers,
            'equipmentTypes' => $this->equipmentTypes,
            'selectedEquipmentType' => $this->selectedEquipmentType,
            'equipmentTypeBrands' => $this->equipmentTypeBrands,
            'equipmentTypeModels' => $this->equipmentTypeModels,
            'equipmentDocuments' => $this->buildEquipmentDocuments(),
            'analysisServices' => $this->analysisServices,
            'statusFlows' => $this->statusFlows,
            'serviceItemsSubtotal' => number_format($this->serviceItemsSubtotal(), 2, ',', '.'),
            'documentPreviewTitle' => trans('service-order::messages.equipment_type_document_preview_title'),
            'documentPreviewPendingFile' => trans('service-order::messages.equipment_type_pending_file_preview'),
        ]);
    }

    private function hydrateFromServiceOrder(ServiceOrder $serviceOrder): void
    {
        $this->serviceOrderForm = $serviceOrder->toManagementForm();
        $this->serviceItems = $serviceOrder->toManagementServiceItems();
        $this->dynamicFields = $serviceOrder->toManagementDynamicFields();
        $this->currentServiceOrder = $serviceOrder->toManagementMeta();
        $this->selectedEquipmentType = ($this->serviceOrderForm['equipment_type_id'] ?? '') !== ''
            ? ($this->equipmentCatalogService()->findEquipmentTypeDetail((string) $this->serviceOrderForm['equipment_type_id']) ?? [])
            : [];
        $this->selectedEquipmentBrandId = ($this->serviceOrderForm['equipment_type_id'] ?? '') !== ''
            ? $this->equipmentCatalogService()->resolveBrandId(
                (string) $this->serviceOrderForm['equipment_type_id'],
                (string) ($this->serviceOrderForm['equipment_brand'] ?? '')
            )
            : null;

        $this->assignSelectedCustomer((string) $serviceOrder->customer_id);
    }

    private function assignSelectedCustomer(string $customerId): void
    {
        $summary = $this->customerService()->findCustomerSummary($customerId);

        if (! is_array($summary)) {
            $this->serviceOrderForm['customer_id'] = '';
            $this->selectedCustomer = [];
            $this->customerCorrection = $this->emptyCustomerCorrection();
            $this->customerForCorrectionModal = null;

            return;
        }

        $this->serviceOrderForm['customer_id'] = (string) $summary['id'];
        $this->selectedCustomer = $summary;
        $this->customerCorrection = $this->mapCustomerCorrection($summary);
        $this->customerForCorrectionModal = $this->customerService()->findCustomerForEditingOrFail((string) $summary['id']);
    }

    private function loadAvailableCustomers(string $search = ''): void
    {
        $this->availableCustomers = $this->customerService()->searchCustomers($search, 20);
    }

    private function applyEquipmentType(string $equipmentTypeId): void
    {
        $equipmentType = $this->equipmentCatalogService()->findEquipmentTypeDetail($equipmentTypeId);

        $this->serviceOrderForm['equipment_type_id'] = $equipmentTypeId;
        $this->serviceOrderForm['selected_document_id'] = '';
        $this->serviceOrderForm['equipment_brand'] = '';
        $this->serviceOrderForm['equipment_model'] = '';
        $this->selectedEquipmentBrandId = null;
        $this->equipmentBrandSearch = '';
        $this->equipmentBrandSearchActive = false;
        $this->equipmentTypeBrands = [];
        $this->equipmentModelSearch = '';
        $this->equipmentModelSearchActive = false;
        $this->equipmentTypeModels = [];
        $this->selectedEquipmentType = $equipmentType ?? [];
        $this->dynamicFields = is_array($equipmentType) ? (array) ($equipmentType['fields'] ?? []) : [];
    }

    private function resetEquipmentContext(): void
    {
        $this->serviceOrderForm['equipment_type_id'] = '';
        $this->serviceOrderForm['selected_document_id'] = '';
        $this->serviceOrderForm['equipment_brand'] = '';
        $this->serviceOrderForm['equipment_model'] = '';
        $this->selectedEquipmentType = [];
        $this->dynamicFields = [];
        $this->selectedEquipmentBrandId = null;
        $this->equipmentTypeBrands = [];
        $this->equipmentTypeModels = [];
    }

    private function applyBrandSelection(array $brand): void
    {
        $this->serviceOrderForm['equipment_brand'] = (string) ($brand['name'] ?? '');
        $this->selectedEquipmentBrandId = blank($brand['id'] ?? null) ? null : (string) $brand['id'];
        $this->serviceOrderForm['equipment_model'] = '';
        $this->equipmentModelSearch = '';
        $this->equipmentModelSearchActive = false;
        $this->equipmentTypeModels = [];
    }

    private function buildEquipmentDocuments(): array
    {
        return collect($this->selectedEquipmentType['documents'] ?? [])
            ->values()
            ->map(function (array $document): array {
                $previewText = null;
                $documentType = (string) ($document['document_type'] ?? '');

                if ($documentType === 'editable_template' && filled($document['template_content'] ?? null)) {
                    $previewText = str_replace(
                        [
                            '{{dados_cliente}}',
                            '{{equipamento_modelo}}',
                            '{{numero_ordem_servico}}',
                        ],
                        [
                            (string) ($this->selectedCustomer['name'] ?? ''),
                            (string) ($this->serviceOrderForm['equipment_model'] ?? ''),
                            (string) ($this->currentServiceOrder['order_number'] ?? ''),
                        ],
                        (string) $document['template_content']
                    );
                }

                return [
                    'id' => (string) ($document['id'] ?? ''),
                    'title' => (string) (($document['title'] ?? '') ?: trans('service-order::messages.equipment_type_document_without_title')),
                    'document_type' => $documentType,
                    'document_type_label' => $documentType === 'editable_template'
                        ? trans('service-order::messages.equipment_type_template_label')
                        : 'PDF',
                    'icon' => $documentType === 'editable_template'
                        ? 'ti-file-description'
                        : 'ti-file-type-pdf',
                    'preview_url' => $documentType === 'fixed_pdf' && filled($document['path'] ?? null)
                        ? route('service-order-equipment-types-document-file', ['id' => $document['id']])
                        : null,
                    'preview_text' => $previewText,
                    'is_selected' => (string) ($this->serviceOrderForm['selected_document_id'] ?? '') === (string) ($document['id'] ?? ''),
                ];
            })
            ->all();
    }

    private function serviceItemsSubtotal(): float
    {
        return collect($this->serviceItems)->sum(function (array $item): float {
            $unitValue = max(0, round((float) ($item['unit_value'] ?? 0), 2));
            $discountValue = min(max(0, round((float) ($item['discount_value'] ?? 0), 2)), $unitValue);

            return max(0, $unitValue - $discountValue);
        });
    }

    private function recalculateItemTotal(int $index): void
    {
        if (! isset($this->serviceItems[$index])) {
            return;
        }

        $unitValue = max(0, round((float) ($this->serviceItems[$index]['unit_value'] ?? 0), 2));
        $discountValue = min(max(0, round((float) ($this->serviceItems[$index]['discount_value'] ?? 0), 2)), $unitValue);
        $totalValue = max(0, $unitValue - $discountValue);

        $this->serviceItems[$index]['unit_value'] = number_format($unitValue, 2, '.', '');
        $this->serviceItems[$index]['discount_value'] = number_format($discountValue, 2, '.', '');
        $this->serviceItems[$index]['total_value'] = number_format($totalValue, 2, '.', '');
    }

    private function sanitizeCustomerPayload(array $payload): array
    {
        return [
            'name' => trim((string) ($payload['name'] ?? '')),
            'person' => (string) ($payload['person'] ?? 'F'),
            'cpf_cnpj' => trim((string) ($payload['cpf_cnpj'] ?? '')),
            'email' => trim((string) ($payload['email'] ?? '')),
            'cellphone' => blank($payload['cellphone'] ?? null) ? null : trim((string) $payload['cellphone']),
            'zip_code' => trim((string) ($payload['zip_code'] ?? '')),
            'address' => trim((string) ($payload['address'] ?? '')),
            'number' => trim((string) ($payload['number'] ?? '')),
            'neighborhood' => trim((string) ($payload['neighborhood'] ?? '')),
            'city' => trim((string) ($payload['city'] ?? '')),
            'state' => strtoupper(trim((string) ($payload['state'] ?? ''))),
            'status' => '1',
        ];
    }

    private function resetNewCustomerForm(): void
    {
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
    }

    private function emptyCustomerCorrection(): array
    {
        return [
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
    }

    private function mapCustomerCorrection(array $summary): array
    {
        return [
            'id' => (string) ($summary['id'] ?? ''),
            'name' => (string) ($summary['name'] ?? ''),
            'person' => (string) ($summary['person'] ?? 'F'),
            'cpf_cnpj' => (string) ($summary['cpf_cnpj'] ?? ''),
            'email' => (string) ($summary['email'] ?? ''),
            'cellphone' => (string) ($summary['cellphone'] ?? ''),
            'zip_code' => (string) ($summary['zip_code'] ?? ''),
            'address' => (string) ($summary['address'] ?? ''),
            'number' => (string) ($summary['number'] ?? ''),
            'neighborhood' => (string) ($summary['neighborhood'] ?? ''),
            'city' => (string) ($summary['city'] ?? ''),
            'state' => (string) ($summary['state'] ?? ''),
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

    private function recordService(): ServiceOrderRecordServiceInterface
    {
        return app(ServiceOrderRecordServiceInterface::class);
    }

    private function catalogService(): ServiceOrderCatalogServiceInterface
    {
        return app(ServiceOrderCatalogServiceInterface::class);
    }

    private function customerService(): ServiceOrderCustomerServiceInterface
    {
        return app(ServiceOrderCustomerServiceInterface::class);
    }

    private function equipmentCatalogService(): ServiceOrderEquipmentCatalogServiceInterface
    {
        return app(ServiceOrderEquipmentCatalogServiceInterface::class);
    }
}
