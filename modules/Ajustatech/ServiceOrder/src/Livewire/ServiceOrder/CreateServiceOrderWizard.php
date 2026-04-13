<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderCustomerServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderEquipmentCatalogServiceInterface;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderRecordServiceInterface;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateServiceOrderWizard extends Component
{
    public bool $showCreateWizardModal = false;

    public bool $showCreateWizardCustomerSelectModal = false;

    public bool $showCreateWizardCustomerCreateModal = false;

    public string $createWizardCustomerSearch = '';

    public ?string $createWizardCustomerSelectedId = null;

    public array $equipmentTypes = [];

    public array $wizardCustomerCandidates = [];

    public array $selectedWizardCustomer = [];

    public int $createWizardCustomerCreateKey = 0;

    public array $createWizard = [
        'equipment_type_id' => '',
        'customer_id' => '',
    ];

    public function openWizard(): void
    {
        if ($this->equipmentTypes === []) {
            $this->equipmentTypes = $this->equipmentCatalogService()->listActiveEquipmentTypes();
        }

        $this->showCreateWizardModal = true;
    }

    public function closeCreateWizard(): void
    {
        $this->showCreateWizardModal = false;
        $this->showCreateWizardCustomerSelectModal = false;
        $this->showCreateWizardCustomerCreateModal = false;
        $this->createWizardCustomerSearch = '';
        $this->wizardCustomerCandidates = [];
        $this->selectedWizardCustomer = [];
        $this->createWizard = [
            'equipment_type_id' => '',
            'customer_id' => '',
        ];
        $this->createWizardCustomerSelectedId = null;
        $this->resetValidation([
            'createWizard.*',
            'createWizardCustomerSearch',
            'createWizardCustomerSelectedId',
        ]);
    }

    public function openCreateWizardCustomerSelectModal(): void
    {
        $this->createWizardCustomerSelectedId = filled($this->createWizard['customer_id'])
            ? (string) $this->createWizard['customer_id']
            : null;
        $this->showCreateWizardCustomerSelectModal = true;
    }

    public function openCreateWizardCustomerCreateModal(): void
    {
        $this->createWizardCustomerCreateKey++;
        $this->showCreateWizardCustomerCreateModal = true;
    }

    public function closeCreateWizardCustomerSelectModal(): void
    {
        $this->showCreateWizardCustomerSelectModal = false;
        $this->createWizardCustomerSearch = '';
        $this->wizardCustomerCandidates = [];
        $this->createWizardCustomerSelectedId = null;
        $this->resetValidation(['createWizardCustomerSearch', 'createWizardCustomerSelectedId']);
    }

    public function closeCreateWizardCustomerCreateModal(): void
    {
        $this->showCreateWizardCustomerCreateModal = false;
    }

    public function updatedCreateWizardCustomerSearch(): void
    {
        $this->createWizardCustomerSearch = mb_substr(trim($this->createWizardCustomerSearch), 0, 120);
        $this->createWizardCustomerSelectedId = null;

        if ($this->createWizardCustomerSearch === '') {
            $this->wizardCustomerCandidates = [];

            return;
        }

        $this->wizardCustomerCandidates = $this->customerService()->searchCustomers($this->createWizardCustomerSearch, 20);
    }

    public function selectCreateWizardCustomerCandidate(string $customerId): void
    {
        $this->createWizardCustomerSelectedId = $customerId;
    }

    public function confirmCreateWizardSelectedCustomer(): void
    {
        $validated = $this->validate([
            'createWizardCustomerSelectedId' => ['required', 'uuid', 'exists:customers,id'],
        ], [], [
            'createWizardCustomerSelectedId' => $this->customerLabel(),
        ]);

        $this->createWizard['customer_id'] = $validated['createWizardCustomerSelectedId'];
        $this->selectedWizardCustomer = $this->customerService()->findCustomerSelectionOrFail($this->createWizard['customer_id']);
        $this->closeCreateWizardCustomerSelectModal();
    }

    #[On('customer-created')]
    public function handleCustomerCreated(string $id = ''): void
    {
        if (! $this->showCreateWizardModal || ! $this->showCreateWizardCustomerCreateModal || blank($id)) {
            return;
        }

        $this->createWizard['customer_id'] = $id;
        $this->createWizardCustomerSelectedId = $id;
        $this->selectedWizardCustomer = $this->customerService()->findCustomerSelectionOrFail($id);
        $this->closeCreateWizardCustomerCreateModal();
    }

    public function confirmCreateWizard(): void
    {
        $validated = $this->validate([
            'createWizard.equipment_type_id' => ['required', 'uuid', 'exists:service_order_equipment_types,id'],
            'createWizard.customer_id' => ['required', 'uuid', 'exists:customers,id'],
        ], [], [
            'createWizard.equipment_type_id' => $this->equipmentTypeLabel(),
            'createWizard.customer_id' => $this->customerLabel(),
        ]);

        $equipmentType = $this->equipmentCatalogService()->findEquipmentTypeDetailOrFail($validated['createWizard']['equipment_type_id']);

        $created = $this->recordService()->createServiceOrder([
            'customer_id' => $validated['createWizard']['customer_id'],
            'equipment_type_id' => $validated['createWizard']['equipment_type_id'],
            'selected_document_id' => null,
            'equipment_brand' => null,
            'equipment_model' => null,
            'equipment_serial_number' => null,
            'dynamic_fields' => (array) ($equipmentType['fields'] ?? []),
            'service_items' => [],
        ]);

        $this->closeCreateWizard();
        $this->redirectRoute('service-order-edit', ['serviceOrder' => $created->id]);
    }

    public function render()
    {
        return view('service-order::livewire.service-order.create-service-order-wizard', [
            'selectedWizardCustomer' => $this->selectedWizardCustomer,
            'wizardCustomerCandidates' => collect($this->wizardCustomerCandidates),
        ]);
    }

    private function customerLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Customer' : 'Cliente';
    }

    private function equipmentTypeLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Equipment type' : 'Tipo de equipamento';
    }

    private function customerService(): ServiceOrderCustomerServiceInterface
    {
        return app(ServiceOrderCustomerServiceInterface::class);
    }

    private function equipmentCatalogService(): ServiceOrderEquipmentCatalogServiceInterface
    {
        return app(ServiceOrderEquipmentCatalogServiceInterface::class);
    }

    private function recordService(): ServiceOrderRecordServiceInterface
    {
        return app(ServiceOrderRecordServiceInterface::class);
    }
}
