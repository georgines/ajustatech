<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\ServiceCatalogService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceCatalog extends Component
{
    public string $title = 'Servicos cadastrados';

    public function toggleStatus(string $id): void
    {
        $service = ServiceCatalogService::query()->findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);
    }

    public function render()
    {
        $services = ServiceCatalogService::query()->orderBy('name')->get();

        return view('service-order::livewire.show-service-catalog', [
            'services' => $services,
        ]);
    }
}
