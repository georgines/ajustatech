<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Financial\Database\Models\FinancialPaymentMethod;
use Ajustatech\Financial\Services\CardBrandService;
use Ajustatech\Financial\Services\PaymentMethodService;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class PaymentMethodManagement extends Component
{
    use SwitchAlertDispatch;

    public $title;
    public $mode = 'create';
    public $methodId = null;
    public $type = FinancialPaymentMethod::TYPE_DINHEIRO;
    public $name = '';

    public $fixedCost;
    public $percentCost;
    public $brand = '';
    public $installments;
    public $receiptChannel = '';
    public $editingCostIndex = null;

    public $costs = [];
    public $types = [];
    public $receiptChannels = [];
    public $brands = [];
    public $installmentOptions = [];

    public function mount(PaymentMethodService $service, CardBrandService $brandService, ?string $id = null): void
    {
        $this->title = trans('financial::messages.payment_method_create_title');
        $this->types = $service->getTypes();
        $this->receiptChannels = $service->getReceiptChannels();
        $this->brands = $brandService->listActiveBrandNames();
        $this->installmentOptions = range(1, 21);

        if ($id) {
            $method = $service->findMethod($id);
            $this->mode = 'edit';
            $this->methodId = $method->id;
            $this->type = $method->type;
            $this->name = $method->name;
            $this->costs = $method->costs
                ->map(fn ($cost) => [
                    'fixed_cost' => (float) $cost->fixed_cost,
                    'percent_cost' => (float) $cost->percent_cost,
                    'brand' => $cost->brand,
                    'installments' => $cost->installments,
                    'receipt_channel' => $cost->receipt_channel,
                ])
                ->toArray();
            $this->title = trans('financial::messages.payment_method_edit_title');
        }
    }

    public function updatedType(): void
    {
        if (!$this->isCardType()) {
            $this->brand = '';
            $this->installments = null;
            $this->receiptChannel = '';
        }
    }

    public function addCost(): void
    {
        $this->validate($this->costRules(), [], $this->validationAttributes());

        if ($this->editingCostIndex !== null) {
            $this->updateCost();
            return;
        }

        $this->costs[] = [
            'fixed_cost' => (float) $this->fixedCost,
            'percent_cost' => (float) $this->percentCost,
            'brand' => $this->brand ?: null,
            'installments' => $this->installments ? (int) $this->installments : null,
            'receipt_channel' => $this->receiptChannel ?: null,
        ];

        $this->resetCostForm();
    }

    public function editCost(int $index): void
    {
        if (!array_key_exists($index, $this->costs)) {
            return;
        }

        $this->editingCostIndex = $index;
        $cost = $this->costs[$index];
        $this->fixedCost = $cost['fixed_cost'];
        $this->percentCost = $cost['percent_cost'];
        $this->brand = $cost['brand'] ?? '';
        $this->installments = $cost['installments'] ?? null;
        $this->receiptChannel = $cost['receipt_channel'] ?? '';
    }

    public function updateCost(): void
    {
        if ($this->editingCostIndex === null || !array_key_exists($this->editingCostIndex, $this->costs)) {
            return;
        }

        $this->validate($this->costRules(), [], $this->validationAttributes());

        $this->costs[$this->editingCostIndex] = [
            'fixed_cost' => (float) $this->fixedCost,
            'percent_cost' => (float) $this->percentCost,
            'brand' => $this->brand ?: null,
            'installments' => $this->installments ? (int) $this->installments : null,
            'receipt_channel' => $this->receiptChannel ?: null,
        ];

        $this->cancelCostEdition();
    }

    public function save(PaymentMethodService $service)
    {
        $this->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
        ], [], $this->validationAttributes());

        if (empty($this->costs)) {
            $this->addError('costs', trans('financial::messages.payment_method_cost_required'));
            return;
        }

        try {
            if ($this->mode === 'edit' && $this->methodId) {
                $service->updateMethodWithCosts($this->methodId, $this->type, $this->name, $this->costs);
            } else {
                $service->createMethodWithCosts($this->type, $this->name, $this->costs);
            }
        } catch (InvalidArgumentException $exception) {
            $this->addError('costs', $exception->getMessage());
            return;
        }

        return redirect()->route('financial-payment-methods-show');
    }

    public function confirmRemoveCost(int $index): void
    {
        $this->dispatchConfirmation(trans('financial::messages.confirm_delete_cost_rule'))
            ->to('remove-cost-rule', index: $index)
            ->typeWarning()
            ->setButtonOK(trans('financial::messages.confirm_yes'))
            ->setButtonCancel(trans('financial::messages.confirm_no'))
            ->run();
    }

    #[On('remove-cost-rule')]
    public function removeCost(int $index): void
    {
        if (!array_key_exists($index, $this->costs)) {
            return;
        }

        unset($this->costs[$index]);
        $this->costs = array_values($this->costs);

        if ($this->editingCostIndex !== null && $this->editingCostIndex === $index) {
            $this->cancelCostEdition();
        }
    }

    public function duplicateCost(int $index): void
    {
        if (!array_key_exists($index, $this->costs)) {
            return;
        }

        $copy = $this->costs[$index];
        $this->costs[] = $copy;
        $newIndex = array_key_last($this->costs);

        if ($newIndex === null) {
            return;
        }

        $this->editCost((int) $newIndex);
    }

    private function costRules(): array
    {
        $rules = [
            'fixedCost' => 'required|numeric|min:0',
            'percentCost' => 'required|numeric|min:0',
        ];

        if (in_array($this->type, [FinancialPaymentMethod::TYPE_CARTAO_CREDITO, FinancialPaymentMethod::TYPE_CARTAO_DEBITO], true)) {
            $rules['brand'] = 'required|string|max:100';
            $rules['installments'] = 'required|integer|min:1|max:21';
            $rules['receiptChannel'] = 'required|in:maquina,telefone,link';
        }

        return $rules;
    }

    private function resetCostForm(): void
    {
        $this->fixedCost = null;
        $this->percentCost = null;
        $this->brand = '';
        $this->installments = null;
        $this->receiptChannel = '';
    }

    public function cancelCostEdition(): void
    {
        $this->editingCostIndex = null;
        $this->resetCostForm();
    }

    public function isCardType(): bool
    {
        return in_array(
            $this->type,
            [FinancialPaymentMethod::TYPE_CARTAO_CREDITO, FinancialPaymentMethod::TYPE_CARTAO_DEBITO],
            true
        );
    }

    private function validationAttributes(): array
    {
        return [
            'type' => trans('financial::messages.payment_method_type'),
            'name' => trans('financial::messages.payment_method_name'),
            'fixedCost' => trans('financial::messages.payment_method_fixed_cost'),
            'percentCost' => trans('financial::messages.payment_method_percent_cost'),
            'brand' => trans('financial::messages.payment_method_brand'),
            'installments' => trans('financial::messages.payment_method_installments'),
            'receiptChannel' => trans('financial::messages.payment_method_receipt_channel'),
        ];
    }

    public function render()
    {
        return view('financial::livewire.payment-method-management');
    }
}
