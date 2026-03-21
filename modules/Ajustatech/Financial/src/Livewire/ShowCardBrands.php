<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\Financial\Services\CardBrandService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowCardBrands extends Component
{
    use SwitchAlertDispatch;

    public $title;
    public $brands = [];

    public function mount(CardBrandService $service): void
    {
        $this->title = trans('financial::messages.card_brands_title');
        $this->brands = $service->listBrands();
    }

    public function confirmDelete(string $id): void
    {
        $this->dispatchConfirmation(trans('financial::messages.confirm_delete_card_brand'))
            ->to('delete-card-brand', id: $id)
            ->typeWarning()
            ->setButtonOK(trans('financial::messages.confirm_yes'))
            ->setButtonCancel(trans('financial::messages.confirm_no'))
            ->run();
    }

    #[On('delete-card-brand')]
    public function deleteCardBrand(string $id, CardBrandService $service): void
    {
        $service->delete($id);
        $this->brands = $service->listBrands();
    }

    public function render()
    {
        return view('financial::livewire.show-card-brands');
    }
}