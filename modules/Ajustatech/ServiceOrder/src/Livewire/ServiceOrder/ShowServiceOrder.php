<?php

namespace Ajustatech\ServiceOrder\Livewire\ServiceOrder;

use Ajustatech\Customer\Database\Models\Customer;
use Ajustatech\ServiceOrder\Services\ServiceOrder\Contracts\ServiceOrderServiceInterface;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('core::layouts.app')]
class ShowServiceOrder extends Component
{
    use WithPagination;

    public string $title = '';

    public string $search = '';

    public ?string $statusFlowId = null;

    public ?string $openedFrom = null;

    public ?string $openedTo = null;

    public int $limitePerPage = 10;

    public bool $showCreateWizardModal = false;

    public bool $showCreateWizardCustomerSelectModal = false;

    public bool $showCreateWizardCustomerCreateModal = false;

    public string $createWizardCustomerSearch = '';

    public ?string $createWizardCustomerSelectedId = null;

    public int $createWizardCustomerCreateKey = 0;

    public array $createWizard = [
        'equipment_type_id' => '',
        'customer_id' => '',
    ];

    public function mount(): void
    {
        $this->title = trans('service-order::messages.title');
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 120);
        $this->resetPage();
    }

    public function updatedStatusFlowId(): void
    {
        $this->statusFlowId = blank($this->statusFlowId) ? null : $this->statusFlowId;
        $this->resetPage();
    }

    public function updatedOpenedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedOpenedTo(): void
    {
        $this->resetPage();
    }

    public function updatedLimitePerPage(): void
    {
        if (! in_array($this->limitePerPage, [10, 30, 50, 100], true)) {
            $this->limitePerPage = 10;
        }

        $this->resetPage();
    }

    public function deleteServiceOrder(string $id, ServiceOrderServiceInterface $service): void
    {
        $service->deleteServiceOrder($id);
        $this->resetPage();
    }

    public function duplicateServiceOrder(string $id, ServiceOrderServiceInterface $service): void
    {
        $service->duplicateServiceOrder($id);
        $this->resetPage();
    }

    public function openCreateWizard(): void
    {
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

        $equipmentType = $service->listActiveEquipmentTypes()
            ->firstWhere('id', (string) $validated['createWizard']['equipment_type_id']);

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
        $workingDays = $service->workingDays();
        $holidays = $service->holidays();
        $serviceOrders = $service->listServiceOrders(
            $this->search,
            $this->statusFlowId,
            $this->openedFrom,
            $this->openedTo,
            $this->limitePerPage
        );

        $rows = $serviceOrders->through(function ($serviceOrder) use ($workingDays, $holidays) {
            $documents = $serviceOrder->equipmentType?->documents ?? collect();
            $snapshot = $serviceOrder->customer_snapshot_json ?? [];

            $documentsMapped = $documents
                ->map(function ($document) use ($snapshot, $serviceOrder) {
                    $templatePreview = null;

                    if ($document->document_type === 'editable_template' && filled($document->template_content)) {
                        $templatePreview = str_replace(
                            [
                                '{{dados_cliente}}',
                                '{{equipamento_modelo}}',
                                '{{numero_ordem_servico}}',
                            ],
                            [
                                (string) ($snapshot['name'] ?? ''),
                                (string) ($serviceOrder->equipment_model ?? ''),
                                (string) ($serviceOrder->order_number ?? ''),
                            ],
                            $document->template_content
                        );
                    }

                    return [
                        'id' => $document->id,
                        'title' => $document->title,
                        'type' => $document->document_type,
                        'path' => $document->path,
                        'template_preview' => $templatePreview,
                    ];
                })
                ->values()
                ->all();

            return [
                'id' => $serviceOrder->id,
                'order_number' => $serviceOrder->order_number,
                'business_days' => $serviceOrder->businessDaysSinceCreation(
                    $workingDays,
                    $holidays
                ),
                'customer_name' => $snapshot['name'] ?? $serviceOrder->customer?->name,
                'status_name' => $serviceOrder->statusFlow?->name,
                'status_code' => $serviceOrder->statusFlow?->code,
                'documents' => $documentsMapped,
            ];
        });

        $selectedWizardCustomer = filled($this->createWizard['customer_id'])
            ? Customer::query()->find($this->createWizard['customer_id'])
            : null;

        return view('service-order::livewire.service-order.show-service-order', [
            'serviceOrders' => $rows,
            'statusFlows' => $service->listStatusFlows(),
            'equipmentTypes' => $service->listActiveEquipmentTypes(),
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
