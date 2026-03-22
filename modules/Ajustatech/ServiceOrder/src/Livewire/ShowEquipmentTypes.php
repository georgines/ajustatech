<?php

namespace Ajustatech\ServiceOrder\Livewire;

use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowEquipmentTypes extends Component
{
    public string $title = 'Tipos de Equipamento';

    public function toggleStatus(string $id): void
    {
        $equipmentType = EquipmentType::query()->findOrFail($id);
        $equipmentType->update([
            'is_active' => !$equipmentType->is_active,
        ]);
    }

    public function render()
    {
        $equipmentTypes = EquipmentType::query()
            ->withCount('fields')
            ->orderBy('name')
            ->get();

        return view('service-order::livewire.show-equipment-types', [
            'equipmentTypes' => $equipmentTypes,
        ]);
    }
}
