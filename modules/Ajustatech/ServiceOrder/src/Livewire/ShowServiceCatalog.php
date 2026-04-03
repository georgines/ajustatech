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
        $service = ServiceCatalogService::findOrFailById($id);
        $service->toggleActiveStatus();
    }

    public function render()
    {
        $services = ServiceCatalogService::getListingWithStepsCount();

        return view('service-order::livewire.show-service-catalog', [
            'services' => $services,
        ]);
    }
}
