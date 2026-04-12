<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentType;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
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

    public int $createWizardCustomerCreateKey = 0;

    public array $createWizard = [
        'equipment_type_id' => '',
        'customer_id' => '',
    ];

    public function openWizard(): void
    {
        if ($this->equipmentTypes === []) {
            $service = app(ServiceOrderServiceInterface::class);

            $this->equipmentTypes = $service->listActiveEquipmentTypes()
                ->map(fn ($equipmentType) => [
                    'id' => (string) $equipmentType->id,
                    'name' => (string) $equipmentType->name,
                ])
                ->values()
                ->all();
        }

        $this->showCreateWizardModal = true;
    }

    public function closeCreateWizard(): void
    {
        $this->showCreateWizardModal = false;
        $this->showCreateWizardCustomerSelectModal = false;
        $this->showCreateWizardCustomerCreateModal = false;
        $this->createWizardCustomerSearch = '';
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
            'createWizardCustomerSelectedId' => trans('service-order::messages.customer'),
        ]);

        $this->createWizard['customer_id'] = $validated['createWizardCustomerSelectedId'];
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
        $this->closeCreateWizardCustomerCreateModal();
    }

    public function confirmCreateWizard(): void
    {
        $service = app(ServiceOrderServiceInterface::class);

        $validated = $this->validate([
            'createWizard.equipment_type_id' => ['required', 'uuid', 'exists:service_order_equipment_types,id'],
            'createWizard.customer_id' => ['required', 'uuid', 'exists:customers,id'],
        ], [], [
            'createWizard.equipment_type_id' => trans('service-order::messages.equipment_type'),
            'createWizard.customer_id' => trans('service-order::messages.customer'),
        ]);

        $equipmentType = ServiceOrderEquipmentType::query()
            ->where('is_active', true)
            ->with(['fields:id,equipment_type_id,field_type,label,placeholder,is_required,default_text'])
            ->findOrFail($validated['createWizard']['equipment_type_id']);

        $dynamicFields = collect($equipmentType?->fields ?? [])
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

        $created = $service->createServiceOrder([
            'customer_id' => $validated['createWizard']['customer_id'],
            'equipment_type_id' => $validated['createWizard']['equipment_type_id'],
            'selected_document_id' => null,
            'equipment_brand' => null,
            'equipment_model' => null,
            'equipment_serial_number' => null,
            'dynamic_fields' => $dynamicFields,
            'service_items' => [],
        ]);

        $this->closeCreateWizard();
        $this->redirectRoute('service-order-edit', ['serviceOrder' => $created->id]);
    }

    public function render(ServiceOrderServiceInterface $service)
    {
        $selectedWizardCustomer = filled($this->createWizard['customer_id'])
            ? Customer::query()->find($this->createWizard['customer_id'])
            : null;

        return view('service-order::livewire.service-order.create-service-order-wizard', [
            'selectedWizardCustomer' => $selectedWizardCustomer,
            'selectedWizardCustomerDocumentMasked' => $selectedWizardCustomer
                ? $this->maskDocument((string) $selectedWizardCustomer->cpf_cnpj)
                : '',
            'wizardCustomerCandidates' => filled(trim($this->createWizardCustomerSearch))
                ? $service->searchCustomers($this->createWizardCustomerSearch, 20)->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'document_masked' => $this->maskDocument((string) $customer->cpf_cnpj),
                    ];
                })
                : collect(),
        ]);
    }

    private function maskDocument(string $document): string
    {
        $digits = preg_replace('/\D+/', '', $document);

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3) . '.***.***-' . substr($digits, 9, 2);
        }

        if (strlen($digits) === 14) {
            return substr($digits, 0, 2) . '.***.***/' . substr($digits, 8, 4) . '-' . substr($digits, 12, 2);
        }

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return substr($digits, 0, 2) . str_repeat('*', max(0, strlen($digits) - 4)) . substr($digits, -2);
    }
}
