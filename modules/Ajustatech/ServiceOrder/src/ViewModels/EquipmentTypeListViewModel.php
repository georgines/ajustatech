<?php

namespace Ajustatech\ServiceOrder\ViewModels;

use Illuminate\Support\Collection;

class EquipmentTypeListViewModel
{
    public static function fromCollection(Collection $equipmentTypes): array
    {
        return [
            'screen' => 'equipment_type_index',
            'vuexy' => [
                'layout' => 'datatable-card',
                'actions' => [
                    ['key' => 'create', 'label' => 'Novo tipo de equipamento'],
                ],
                'columns' => ['name', 'status', 'field_count', 'actions'],
            ],
            'items' => $equipmentTypes->map(function ($equipmentType) {
                return [
                    'id' => $equipmentType->id,
                    'name' => $equipmentType->name,
                    'status' => $equipmentType->is_active ? 'active' : 'inactive',
                    'field_count' => $equipmentType->fields_count ?? $equipmentType->fields->count(),
                    'actions' => ['edit', 'toggle_status', 'show'],
                ];
            })->values()->all(),
        ];
    }
}

