<?php

namespace Ajustatech\ServiceOrder\Livewire\EquipmentType;

use Ajustatech\ServiceOrder\Services\EquipmentType\Contracts\EquipmentTypeServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowEquipmentTypes extends Component
{
    public string $title = '';

    public string $search = '';

    public bool $activeOnly = true;

    public int $limitePerPage = 10;

    public function mount(): void
    {
        $this->title = trans('service-order::messages.equipment_types_title');
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 120);
    }

    public function updatedLimitePerPage(): void
    {
        if (! in_array($this->limitePerPage, [10, 30, 50, 100], true)) {
            $this->limitePerPage = 10;
        }
    }

    public function deleteEquipmentType(string $id, EquipmentTypeServiceInterface $service): void
    {
        $service->deleteEquipmentType($id);
    }

    public function toggleEquipmentTypeStatus(string $id, EquipmentTypeServiceInterface $service): void
    {
        $service->toggleEquipmentTypeStatus($id);
    }

    public function duplicateEquipmentType(string $id, EquipmentTypeServiceInterface $service): void
    {
        $service->duplicateEquipmentType($id);
    }

    public function render(EquipmentTypeServiceInterface $service)
    {
        $status = $this->activeOnly ? 'active' : 'all';
        $equipmentTypes = $service->listEquipmentTypes($this->search, $status, $this->limitePerPage);

        return view('service-order::livewire.equipment-type.show-equipment-types', [
            'equipmentTypes' => $equipmentTypes,
        ]);
    }
}
