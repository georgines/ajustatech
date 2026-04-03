<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Ajustatech\ServiceOrder\Services\ServiceOrderService;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class NewServiceOrderManagement extends Component
{
    use WithFileUploads;
    use SwitchAlertDispatch;

    public string $title = 'Nova Ordem de Servico';
    public string $currentStep = 'customer';
    public bool $isEditMode = false;
    public ?string $orderId = null;

    public int $orderNumberPreview = 0;
    public string $openingDate = '';
    public string $openingTime = '';

    public string $customerSearch = '';
    public array $customerResults = [];
    public ?string $customer_id = null;

    public bool $showCustomerModal = false;

    public ?string $equipment_type_id = null;
    public string $equipment_name = '';
    public ?string $brand = null;
    public ?string $model = null;
    public ?string $serial_number = null;
    public ?string $entry_date = null;
    public ?string $reported_issue = null;

    public array $availableEquipmentTypes = [];
    public array $activeFieldSnapshots = [];
    public array $fieldValues = [];
    public array $uploadedFiles = [];

    public array $availableServices = [];
    public array $selectedServices = [];
    public array $serviceDiscounts = [];
    public ?string $serviceToAddId = null;
    public string $serviceCatalogSearch = '';
    public array $serviceCatalogSearchResults = [];
    public bool $showServiceModal = false;
    public bool $showServiceEditModal = false;
    public ?string $editingServiceId = null;
    public ?string $editServiceSelectionId = null;
    public int $editServiceQty = 1;
    public $editServiceDiscount = '0,00';

    public function mount(EquipmentTypeService $equipmentTypeService, ?string $id = null): void
    {
        $now = now();
        $this->entry_date = $now->toDateString();
        $this->openingDate = $now->format('d/m/Y');
        $this->openingTime = $now->format('H:i');
        $this->availableEquipmentTypes = EquipmentType::getActiveSelectionList();

        $this->refreshAvailableServices();

        if ($id) {
            $this->loadOrderForEditing($id, $equipmentTypeService);
            return;
        }

        $this->orderNumberPreview = ServiceOrder::nextOrderNumberPreview();

        if ($this->equipment_type_id) {
            $this->loadFieldsForEquipmentType($equipmentTypeService);
        }
    }

    public function updatedCustomerSearch(): void
    {
        $search = trim($this->customerSearch);

        if ($search === '' || mb_strlen($search) < 2) {
            $this->customerResults = [];
            return;
        }

        $this->customerResults = Customer::search($search, true, 10)
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'cpf_cnpj' => $customer->cpf_cnpj,
                'email' => $customer->email,
                'cellphone' => $customer->cellphone,
            ])
            ->all();
    }

    public function selectCustomer(string $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->customer_id = $customer->id;
        $this->customerSearch = $customer->name;
        $this->customerResults = [];
        $this->showCustomerModal = false;
        $this->resetErrorBag('customer');
    }

    public function clearSelectedCustomer(): void
    {
        $this->customer_id = null;
        $this->customerSearch = '';
        $this->customerResults = [];
        $this->currentStep = 'customer';
    }

    public function openCustomerModal(): void
    {
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal(): void
    {
        $this->showCustomerModal = false;
    }

    #[On('customer-created')]
    public function handleCustomerCreated(string $id, string $name): void
    {
        $this->customer_id = $id;
        $this->customerSearch = $name;
        $this->customerResults = [];
        $this->showCustomerModal = false;
        $this->currentStep = 'customer';
        $this->resetErrorBag('customer');
    }

    #[On('service-catalog-changed')]
    public function handleServiceCatalogChanged(): void
    {
        $this->refreshAvailableServices();
        $this->showServiceModal = false;
    }

    public function openServiceModal(): void
    {
        $this->showServiceModal = true;
    }

    public function closeServiceModal(): void
    {
        $this->showServiceModal = false;
    }

    public function includeAllRegisteredServices(): void
    {
        foreach ($this->availableServices as $service) {
            $serviceId = (string) $service['id'];
            $this->selectedServices[$serviceId] = max((int) ($this->selectedServices[$serviceId] ?? 0), 1);
            $this->serviceDiscounts[$serviceId] = $this->normalizedDiscount($this->serviceDiscounts[$serviceId] ?? 0);
        }
    }

    public function addServiceToOrder(): void
    {
        if (!$this->serviceToAddId) {
            return;
        }

        $exists = collect($this->availableServices)->contains(fn (array $item) => $item['id'] === $this->serviceToAddId);
        if (!$exists) {
            return;
        }

        $this->selectedServices[$this->serviceToAddId] = max((int) ($this->selectedServices[$this->serviceToAddId] ?? 0), 1);
        $this->serviceDiscounts[$this->serviceToAddId] = $this->normalizedDiscount($this->serviceDiscounts[$this->serviceToAddId] ?? 0);
        $this->serviceToAddId = null;
        $this->serviceCatalogSearch = '';
        $this->serviceCatalogSearchResults = [];
    }

    public function updatedServiceCatalogSearch(): void
    {
        $search = mb_strtolower(trim($this->serviceCatalogSearch));
        if ($search === '' || mb_strlen($search) < 2) {
            $this->serviceCatalogSearchResults = [];
            return;
        }

        $this->serviceCatalogSearchResults = collect($this->availableServices)
            ->filter(function (array $service) use ($search) {
                return str_contains(mb_strtolower((string) Arr::get($service, 'name', '')), $search);
            })
            ->take(10)
            ->values()
            ->all();
    }

    public function selectCatalogService(string $serviceId): void
    {
        $service = collect($this->availableServices)->firstWhere('id', $serviceId);
        if (!$service) {
            return;
        }

        $this->serviceToAddId = $serviceId;
        $this->serviceCatalogSearch = (string) $service['name'];
        $this->serviceCatalogSearchResults = [];
    }

    public function openServiceEditModal(string $serviceId): void
    {
        if (!array_key_exists($serviceId, $this->selectedServices)) {
            return;
        }

        $this->editingServiceId = $serviceId;
        $this->editServiceSelectionId = $serviceId;
        $this->editServiceQty = max((int) Arr::get($this->selectedServices, $serviceId, 1), 1);
        $this->editServiceDiscount = number_format($this->normalizedDiscount($this->serviceDiscounts[$serviceId] ?? 0), 2, ',', '');
        $this->showServiceEditModal = true;
    }

    public function closeServiceEditModal(): void
    {
        $this->showServiceEditModal = false;
        $this->editingServiceId = null;
        $this->editServiceSelectionId = null;
        $this->editServiceQty = 1;
        $this->editServiceDiscount = '0,00';
        $this->resetErrorBag(['editServiceSelectionId', 'editServiceQty', 'editServiceDiscount']);
    }

    public function confirmSaveServiceEdition(): void
    {
        if (!$this->editingServiceId || !$this->editServiceSelectionId) {
            return;
        }

        $this->validate([
            'editServiceSelectionId' => ['required', 'string', 'uuid'],
            'editServiceQty' => ['required', 'integer', 'min:1', 'max:100'],
            'editServiceDiscount' => ['required'],
        ]);

        $normalizedDiscount = $this->parseDecimalToFloat($this->editServiceDiscount);
        if ($normalizedDiscount === null) {
            $this->addError('editServiceDiscount', 'Valor invalido.');
            return;
        }

        $gross = $this->editServiceGrossAmount;
        if ($normalizedDiscount > $gross) {
            $this->addError('editServiceDiscount', 'Valor invalido para este servico de analise.');
            return;
        }

        if (
            $this->editServiceSelectionId !== $this->editingServiceId
            && array_key_exists($this->editServiceSelectionId, $this->selectedServices)
        ) {
            $this->addError('editServiceSelectionId', 'Este servico de analise ja esta adicionado na ordem.');
            return;
        }

        $this->resetErrorBag(['editServiceSelectionId', 'editServiceQty', 'editServiceDiscount']);

        $this->dispatchConfirmation('Confirmar alteracao do servico de analise?')
            ->typeWarning()
            ->setButtonOK('Sim')
            ->setButtonCancel('Nao')
            ->to(
                'service-order-apply-service-edit',
                $this->editingServiceId,
                $this->editServiceSelectionId,
                $this->editServiceQty,
                $normalizedDiscount
            )
            ->run();
    }

    #[On('service-order-apply-service-edit')]
    public function applyServiceEdition(string $oldServiceId, string $newServiceId, int $quantity, mixed $discount): void
    {
        if (!array_key_exists($oldServiceId, $this->selectedServices)) {
            return;
        }

        $qty = max((int) $quantity, 1);
        unset($this->selectedServices[$oldServiceId], $this->serviceDiscounts[$oldServiceId]);

        $this->selectedServices[$newServiceId] = $qty;
        $normalized = min($this->normalizedDiscount($discount), $this->serviceGrossAmount($newServiceId));
        $this->serviceDiscounts[$newServiceId] = $normalized;
        $this->closeServiceEditModal();
    }

    public function confirmRemoveService(string $serviceId): void
    {
        if (!array_key_exists($serviceId, $this->selectedServices)) {
            return;
        }

        $name = (string) Arr::get(collect($this->availableServices)->firstWhere('id', $serviceId), 'name', 'servico de analise');

        $this->dispatchConfirmation('Confirma remover o servico de analise "' . $name . '" desta ordem?')
            ->typeWarning()
            ->setButtonOK('Sim')
            ->setButtonCancel('Nao')
            ->to('service-order-remove-service', $serviceId)
            ->run();
    }

    #[On('service-order-remove-service')]
    public function removeServiceFromOrder(string $serviceId): void
    {
        unset($this->selectedServices[$serviceId]);
        unset($this->serviceDiscounts[$serviceId]);
    }

    public function proceedToOrder(): void
    {
        if (!$this->customer_id) {
            $this->addError('customer', 'Selecione um cliente existente ou cadastre um novo cliente antes de prosseguir.');
            return;
        }

        $this->currentStep = 'order';
    }

    public function updatedEquipmentTypeId(EquipmentTypeService $equipmentTypeService): void
    {
        $this->loadFieldsForEquipmentType($equipmentTypeService);
    }

    public function save(ServiceOrderService $serviceOrderService, EquipmentTypeService $equipmentTypeService)
    {
        $this->resetErrorBag();

        $this->validate([
            'customer_id' => 'required|string|uuid|exists:customers,id',
            'equipment_type_id' => 'required|string|uuid|exists:equipment_types,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'reported_issue' => 'nullable|string',
        ]);

        $this->validateDynamicRequiredFields();
        $this->validateSelectedServices();

        $customer = Customer::findOrFail($this->customer_id);
        $equipmentName = $this->resolveEquipmentName();

        try {
            DB::transaction(function () use ($serviceOrderService, $equipmentTypeService, $customer, $equipmentName) {
                $order = $this->isEditMode && $this->orderId
                    ? $this->updateExistingOrder($this->orderId, $customer->id, $customer->name, $equipmentName, $equipmentTypeService)
                    : $serviceOrderService->create([
                        'equipment_type_id' => $this->equipment_type_id,
                        'customer_id' => $customer->id,
                        'equipment_name' => $equipmentName,
                        'brand' => $this->brand,
                        'model' => $this->model,
                        'serial_number' => $this->serial_number,
                        'entry_date' => $this->entry_date,
                        'reported_issue' => $this->reported_issue,
                    ]);

                $textualValues = [];
                $attachments = [];

                foreach ($this->activeFieldSnapshots as $field) {
                    $slug = Arr::get($field, 'slug');
                    $fieldType = Arr::get($field, 'field_type');

                    if (EquipmentFieldType::isAttachment($fieldType)) {
                        $uploads = Arr::get($this->uploadedFiles, $slug, []);
                        if (!is_array($uploads)) {
                            $uploads = $uploads ? [$uploads] : [];
                        }

                        $attachments[$slug] = collect($uploads)
                            ->filter()
                            ->map(function ($upload) use ($order) {
                                $path = $upload->store('service-orders/' . $order->id, 'public');

                                return [
                                    'disk' => 'public',
                                    'path' => $path,
                                    'original_name' => $upload->getClientOriginalName(),
                                    'mime_type' => $upload->getMimeType(),
                                    'extension' => strtolower($upload->getClientOriginalExtension()),
                                    'size' => $upload->getSize(),
                                ];
                            })
                            ->values()
                            ->all();

                        continue;
                    }

                    $textualValues[$slug] = Arr::get($this->fieldValues, $slug);
                }

                $serviceOrderService->fillFields($order->id, $textualValues, $attachments);
                $serviceOrderService->syncServices($order->id, $this->normalizedServices());
            });
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->getMessageBag());
            return null;
        }

        return redirect()->route('service-order-orders-show');
    }

    public function getCanCreateOrderProperty(): bool
    {
        return filled($this->customer_id);
    }

    public function getSelectedCustomerProperty(): ?array
    {
        if (!$this->customer_id) {
            return null;
        }

        $customer = Customer::find($this->customer_id);
        if (!$customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'cellphone' => $customer->cellphone,
            'cpf_cnpj' => $customer->cpf_cnpj,
        ];
    }

    public function getSelectedServiceRowsProperty(): array
    {
        $catalogById = collect($this->availableServices)->keyBy('id');

        return collect($this->selectedServices)
            ->map(function ($quantity, $serviceId) use ($catalogById) {
                $service = $catalogById->get($serviceId);
                if (!$service) {
                    return null;
                }

                $unit = (float) $service['base_price'];
                $qty = max((int) $quantity, 1);
                $gross = $unit * $qty;
                $discount = min($this->normalizedDiscount(Arr::get($this->serviceDiscounts, $serviceId, 0)), $gross);

                return [
                    'id' => $serviceId,
                    'name' => $service['name'],
                    'base_price' => $unit,
                    'quantity' => $qty,
                    'discount' => $discount,
                    'line_total' => max($gross - $discount, 0),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getFilteredServiceRowsProperty(): array
    {
        return $this->selectedServiceRows;
    }

    public function getServiceSummaryProperty(): array
    {
        $gross = collect($this->selectedServiceRows)
            ->sum(fn (array $row) => ((float) Arr::get($row, 'base_price', 0)) * ((int) Arr::get($row, 'quantity', 0)));

        $discount = collect($this->selectedServiceRows)
            ->sum(fn (array $row) => (float) Arr::get($row, 'discount', 0));
        $net = $gross - $discount;

        return [
            'gross' => $gross,
            'discount' => $discount,
            'net' => $net,
        ];
    }

    public function getProductSummaryProperty(): array
    {
        return [
            'gross' => 0.0,
            'discount' => 0.0,
            'net' => 0.0,
        ];
    }

    public function getEquipmentTotalsProperty(): array
    {
        return [
            'gross' => (float) Arr::get($this->serviceSummary, 'gross', 0) + (float) Arr::get($this->productSummary, 'gross', 0),
            'discount' => (float) Arr::get($this->serviceSummary, 'discount', 0) + (float) Arr::get($this->productSummary, 'discount', 0),
            'net' => (float) Arr::get($this->serviceSummary, 'net', 0) + (float) Arr::get($this->productSummary, 'net', 0),
        ];
    }

    public function render()
    {
        return view('service-order::livewire.new-service-order-management');
    }

    private function loadFieldsForEquipmentType(EquipmentTypeService $equipmentTypeService): void
    {
        if (!$this->equipment_type_id) {
            $this->activeFieldSnapshots = [];
            return;
        }

        $this->activeFieldSnapshots = $equipmentTypeService->buildActiveFieldSnapshots($this->equipment_type_id);

        foreach ($this->activeFieldSnapshots as $field) {
            $slug = Arr::get($field, 'slug');
            if (!array_key_exists($slug, $this->fieldValues)) {
                $this->fieldValues[$slug] = null;
            }
        }
    }

    private function normalizedServices(): array
    {
        return collect($this->selectedServices)
            ->map(function ($quantity, $serviceId) {
                $qty = (int) $quantity;
                if ($qty <= 0) {
                    return null;
                }

                return [
                    'service_catalog_service_id' => $serviceId,
                    'quantity' => $qty,
                    'discount' => $this->normalizedDiscount($this->serviceDiscounts[$serviceId] ?? 0),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function refreshAvailableServices(): void
    {
        $this->availableServices = ServiceCatalogService::getReusableActiveSelectionList();

        $activeIds = collect($this->availableServices)->pluck('id')->all();
        $this->selectedServices = collect($this->selectedServices)
            ->filter(fn ($qty, $serviceId) => in_array($serviceId, $activeIds, true))
            ->all();
        $this->serviceDiscounts = collect($this->serviceDiscounts)
            ->filter(fn ($value, $serviceId) => in_array($serviceId, $activeIds, true))
            ->all();
    }

    private function resolveEquipmentName(): string
    {
        $selected = collect($this->availableEquipmentTypes)
            ->firstWhere('id', $this->equipment_type_id);

        $name = trim((string) Arr::get($selected, 'name', ''));

        return $name !== '' ? $name : 'Equipamento';
    }

    private function normalizedDiscount(mixed $value): float
    {
        $parsed = $this->parseDecimalToFloat($value);
        if ($parsed === null) {
            return 0.0;
        }

        return max($parsed, 0);
    }

    private function serviceGrossAmount(string $serviceId): float
    {
        $service = collect($this->availableServices)->firstWhere('id', $serviceId);
        if (!$service) {
            return 0.0;
        }

        $qty = max((int) Arr::get($this->selectedServices, $serviceId, 1), 1);
        $unit = (float) Arr::get($service, 'base_price', 0);

        return $qty * $unit;
    }

    public function getEditServiceUnitPriceProperty(): float
    {
        if (!$this->editServiceSelectionId) {
            return 0;
        }

        $service = collect($this->availableServices)->firstWhere('id', $this->editServiceSelectionId);

        return (float) Arr::get($service, 'base_price', 0);
    }

    public function getEditServiceGrossAmountProperty(): float
    {
        return max((int) $this->editServiceQty, 1) * $this->editServiceUnitPrice;
    }

    public function getEditServiceNetAmountProperty(): float
    {
        return max($this->editServiceGrossAmount - $this->normalizedDiscount($this->editServiceDiscount), 0);
    }

    private function parseDecimalToFloat(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $normalized);

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function validateDynamicRequiredFields(): void
    {
        $messages = [];

        foreach ($this->activeFieldSnapshots as $field) {
            $slug = (string) Arr::get($field, 'slug');
            $name = (string) Arr::get($field, 'name', $slug);
            $required = (bool) Arr::get($field, 'is_required', false);
            $fieldType = (string) Arr::get($field, 'field_type');

            if (!$required || $slug === '') {
                continue;
            }

            if (EquipmentFieldType::isAttachment($fieldType)) {
                $uploads = Arr::get($this->uploadedFiles, $slug, []);
                if (!is_array($uploads)) {
                    $uploads = $uploads ? [$uploads] : [];
                }

                if (collect($uploads)->filter()->isEmpty()) {
                    $messages['uploadedFiles.' . $slug] = 'O campo "' . $name . '" é obrigatório.';
                }
                continue;
            }

            $value = Arr::get($this->fieldValues, $slug);
            $emptyText = is_string($value) && trim($value) === '';
            if ($value === null || $emptyText) {
                $messages['fieldValues.' . $slug] = 'O campo "' . $name . '" é obrigatório.';
            }
        }

        if (!empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function validateSelectedServices(): void
    {
        Validator::make(
            ['selectedServices' => $this->selectedServices],
            ['selectedServices.*' => ['nullable', 'integer', 'min:1', 'max:100']]
        )->validate();
    }

    private function loadOrderForEditing(string $id, EquipmentTypeService $equipmentTypeService): void
    {
        $order = ServiceOrder::findForEditOrFail($id);

        $this->isEditMode = true;
        $this->orderId = $order->id;
        $this->title = 'Editar Ordem de Servico';
        $this->currentStep = 'order';
        $this->openingDate = optional($order->created_at)->format('d/m/Y') ?: $this->openingDate;
        $this->openingTime = optional($order->created_at)->format('H:i') ?: $this->openingTime;
        $this->customer_id = $order->customer_id;
        $this->customerSearch = $order->customer_name;
        $this->equipment_type_id = $order->equipment_type_id;
        $this->brand = $order->brand;
        $this->model = $order->model;
        $this->serial_number = $order->serial_number;
        $this->entry_date = optional($order->entry_date)->format('Y-m-d');
        $this->reported_issue = $order->reported_issue;

        $this->activeFieldSnapshots = collect($order->fields_snapshot ?? [])
            ->sortBy('sort_order')
            ->values()
            ->all();

        if (empty($this->activeFieldSnapshots)) {
            $this->loadFieldsForEquipmentType($equipmentTypeService);
        }

        $this->fieldValues = [];
        foreach ($order->fieldValues as $value) {
            $this->fieldValues[$value->field_slug] = $value->value_text;
        }

        $this->selectedServices = [];
        $this->serviceDiscounts = [];
        foreach ($order->serviceItems as $item) {
            if (!$item->service_catalog_service_id) {
                continue;
            }

            $serviceId = (string) $item->service_catalog_service_id;
            $this->selectedServices[$serviceId] = max((int) $item->quantity, 1);
            $discount = $item->discount_amount ?? Arr::get($item->service_snapshot, 'discount', 0);
            $this->serviceDiscounts[$serviceId] = $this->normalizedDiscount($discount);
        }
    }

    public function getCanAddCatalogServiceProperty(): bool
    {
        return filled($this->serviceToAddId);
    }

    private function updateExistingOrder(
        string $orderId,
        string $customerId,
        string $customerName,
        string $equipmentName,
        EquipmentTypeService $equipmentTypeService
    ): ServiceOrder {
        $order = ServiceOrder::findOrFailById($orderId);
        $equipmentType = EquipmentType::findOrFailById((string) $this->equipment_type_id);
        $fieldSnapshots = $equipmentTypeService->buildActiveFieldSnapshots($equipmentType->id);

        $order->update([
            'equipment_type_id' => $equipmentType->id,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'equipment_name' => $equipmentName,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'entry_date' => $this->entry_date,
            'reported_issue' => $this->reported_issue,
            'equipment_type_snapshot' => [
                'id' => $equipmentType->id,
                'name' => $equipmentType->name,
                'description' => $equipmentType->description,
            ],
            'fields_snapshot' => $fieldSnapshots,
        ]);

        $this->activeFieldSnapshots = $fieldSnapshots;

        return $order->fresh();
    }
}
