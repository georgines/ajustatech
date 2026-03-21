<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Financial\Services\PaymentMethodService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowPaymentMethods extends Component
{
    use SwitchAlertDispatch;

    public $title;
    public $methods = [];

    public function mount(PaymentMethodService $service): void
    {
        $this->title = trans('financial::messages.payment_methods_title');
        $this->methods = $service->listMethods();
    }

    public function confirmDelete(string $id): void
    {
        $this->dispatchConfirmation(trans('financial::messages.confirm_delete_payment_method'))
            ->to('delete-payment-method', id: $id)
            ->typeWarning()
            ->setButtonOK(trans('financial::messages.confirm_yes'))
            ->setButtonCancel(trans('financial::messages.confirm_no'))
            ->run();
    }

    #[On('delete-payment-method')]
    public function deletePaymentMethod(string $id, PaymentMethodService $service): void
    {
        $service->deleteMethod($id);
        $this->methods = $service->listMethods();
    }

    public function render()
    {
        return view('financial::livewire.show-payment-methods');
    }
}
