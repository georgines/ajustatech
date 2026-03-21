<?php

namespace Ajustatech\Financial\Livewire;

use Ajustatech\Financial\Services\CardBrandService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class CardBrandManagement extends Component
{
    public $title;
    public $mode = 'create';
    public $brandId = null;
    public $name = '';

    public function mount(CardBrandService $service, ?string $id = null): void
    {
        $this->title = trans('financial::messages.card_brand_create_title');

        if ($id) {
            $brand = $service->find($id);
            $this->mode = 'edit';
            $this->brandId = $brand->id;
            $this->name = $brand->name;
            $this->title = trans('financial::messages.card_brand_edit_title');
        }
    }

    public function save(CardBrandService $service)
    {
        $this->validate([
            'name' => 'required|string|max:100|unique:financial_card_brands,name,' . ($this->brandId ?? 'NULL') . ',id',
        ]);

        if ($this->mode === 'edit' && $this->brandId) {
            $service->update($this->brandId, $this->name);
        } else {
            $service->create($this->name);
        }

        return redirect()->route('financial-card-brands-show');
    }

    public function render()
    {
        return view('financial::livewire.card-brand-management');
    }
}