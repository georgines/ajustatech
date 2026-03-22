<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Services\EquipmentTypeService;
use Ajustatech\ServiceOrder\Services\ServiceOrderService;
use Ajustatech\ServiceOrder\Support\EquipmentFieldType;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

#[Layout('core::layouts.app')]
class NewServiceOrderManagement extends Component
{
    use WithFileUploads;

    public string $title = 'Nova Ordem de Servico';
    public string $currentStep = 'customer';

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
    public ?string $serviceToAddId = null;
    public bool $showServiceModal = false;

    public function mount(EquipmentTypeService $equipmentTypeService): void
    {
        $this->entry_date = now()->toDateString();

        $this->availableEquipmentTypes = EquipmentType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->all();

        $this->refreshAvailableServices();

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
            ])
            ->all();
    }

    public function selectCustomer(string $id): void
    {
        $customer = Customer::query()->findOrFail($id);
        $this->customer_id = $customer->id;
        $this->customerSearch = $customer->name;
        $this->customerResults = [];
        $this->showCustomerModal = false;
    }

    public function clearSelectedCustomer(): void
    {
        $this->customer_id = null;
        $this->customerSearch = '';
        $this->currentStep = 'customer';
    }

    public function openCustomerModal(): void
    {
        $this->showCustomerModal = true;
        $this->customer_id = null;
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
        $this->currentStep = 'order';
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
            $this->selectedServices[$service['id']] = max((int) ($this->selectedServices[$service['id']] ?? 0), 1);
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
        $this->serviceToAddId = null;
    }

    public function removeServiceFromOrder(string $serviceId): void
    {
        unset($this->selectedServices[$serviceId]);
    }

    public function proceedToOrder(): void
    {
        if ($this->customer_id) {
            $this->currentStep = 'order';
            return;
        }

        $this->addError('customer', 'Selecione um cliente existente ou cadastre um novo cliente antes de prosseguir.');
        $this->openCustomerModal();
    }

    public function updatedEquipmentTypeId(EquipmentTypeService $equipmentTypeService): void
    {
        $this->loadFieldsForEquipmentType($equipmentTypeService);
    }

    public function updated(string $name): void
    {
        if ($name !== 'equipment_type_id') {
            return;
        }

        $this->loadFieldsForEquipmentType(app(EquipmentTypeService::class));
    }

    public function save(ServiceOrderService $serviceOrderService, EquipmentTypeService $equipmentTypeService)
    {
        $this->validate([
            'customer_id' => 'required|string|uuid|exists:customers,id',
            'equipment_type_id' => 'required|string|uuid|exists:equipment_types,id',
            'equipment_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'entry_date' => 'required|date',
            'reported_issue' => 'nullable|string',
        ]);

        $customer = Customer::query()->findOrFail($this->customer_id);

        $order = $serviceOrderService->create([
            'equipment_type_id' => $this->equipment_type_id,
            'customer_id' => $customer->id,
            'equipment_name' => $this->equipment_name,
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

        try {
            $serviceOrderService->fillFields($order->id, $textualValues, $attachments);
            $serviceOrderService->syncServices($order->id, $this->normalizedServices());
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->getMessageBag());
            return null;
        }

        return redirect()->route('service-order-orders-show');
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
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function refreshAvailableServices(): void
    {
        $this->availableServices = ServiceCatalogService::query()
            ->where('is_active', true)
            ->where('is_reusable', true)
            ->orderBy('name')
            ->get(['id', 'name', 'base_price'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'base_price' => (float) $item->base_price,
            ])
            ->all();

        $activeIds = collect($this->availableServices)->pluck('id')->all();
        $this->selectedServices = collect($this->selectedServices)
            ->filter(fn ($qty, $serviceId) => in_array($serviceId, $activeIds, true))
            ->all();
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

                return [
                    'id' => $serviceId,
                    'name' => $service['name'],
                    'base_price' => (float) $service['base_price'],
                    'quantity' => max((int) $quantity, 1),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('service-order::livewire.new-service-order-management');
    }
}
