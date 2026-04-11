<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\ServiceOrderOld\Database\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowEquipmentTypes extends Component
{
    public string $title = 'Tipos de Equipamento';

    public function toggleStatus(string $id): void
    {
        $equipmentType = EquipmentType::findOrFailById($id);
        $equipmentType->toggleActiveStatus();
    }

    public function render()
    {
        $equipmentTypes = EquipmentType::getListingWithFieldsCount();

        return view('service-order::livewire.show-equipment-types', [
            'equipmentTypes' => $equipmentTypes,
        ]);
    }
}
