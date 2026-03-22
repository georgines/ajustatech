<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Ajustatech\ServiceOrder\Livewire\NewServiceOrderManagement;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ServiceCatalogManagement extends Component
{
    public string $title = 'Cadastro de Servico';
    public string $mode = 'create';
    public ?string $serviceId = null;
    public bool $embedded = false;

    public string $name = '';
    public ?string $description = null;
    public $base_price = 0;
    public bool $is_active = true;

    public function mount(?string $id = null, bool $embedded = false): void
    {
        $this->embedded = $embedded;

        if (!$id) {
            return;
        }

        $service = ServiceCatalogService::query()->findOrFail($id);
        $this->mode = 'edit';
        $this->serviceId = $service->id;
        $this->title = 'Editar Servico';
        $this->name = $service->name;
        $this->description = $service->description;
        $this->base_price = (float) $service->base_price;
        $this->is_active = (bool) $service->is_active;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if ($this->mode === 'edit' && $this->serviceId) {
            ServiceCatalogService::query()->findOrFail($this->serviceId)->update($validated);
        } else {
            ServiceCatalogService::query()->create($validated);
        }

        if ($this->embedded) {
            $this->dispatch('service-catalog-changed');
            $this->dispatch('service-catalog-changed')->to(NewServiceOrderManagement::class);
            $this->resetForm();
            return null;
        }

        return redirect()->route('service-order-services-show');
    }

    private function resetForm(): void
    {
        $this->mode = 'create';
        $this->serviceId = null;
        $this->title = 'Cadastro de Servico';
        $this->name = '';
        $this->description = null;
        $this->base_price = 0;
        $this->is_active = true;
    }

    public function render()
    {
        return view('service-order::livewire.service-catalog-management');
    }
}
