<?php

namespace Ajustatech\ServiceOrder\Database\Seeders;

use Ajustatech\ServiceOrder\Database\Models\AnalysisType;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Ajustatech\ServiceOrder\Database\Models\ServiceOrder;
use Ajustatech\ServiceOrder\Services\AnalysisExecutionService;
use Illuminate\Database\Seeder;

class AnalysisExecutionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $notebookType = AnalysisType::query()->where('slug', 'analise-notebook-completa')->first();
        $computerType = AnalysisType::query()->where('slug', 'analise-computador-completa')->first();
        if (!$notebookType || !$computerType) {
            return;
        }

        $notebookEquipment = EquipmentType::query()->where('name', 'Notebook')->first();
        $desktopEquipment = EquipmentType::query()->whereIn('name', ['Desktop', 'Computador'])->first();

        if (!$notebookEquipment || !$desktopEquipment) {
            return;
        }

        $orderNotebook = ServiceOrder::factory()->create([
            'equipment_type_id' => $notebookEquipment->id,
            'equipment_name' => 'Notebook',
            'brand' => 'Dell',
            'model' => 'Inspiron 15',
            'customer_name' => 'Cliente Demo Notebook',
            'status' => 'open',
            'equipment_type_snapshot' => [
                'id' => $notebookEquipment->id,
                'name' => $notebookEquipment->name,
                'description' => $notebookEquipment->description,
            ],
        ]);

        $orderComputer = ServiceOrder::factory()->create([
            'equipment_type_id' => $desktopEquipment->id,
            'equipment_name' => 'Computador',
            'brand' => 'Custom',
            'model' => 'ATX Ryzen',
            'customer_name' => 'Cliente Demo Computador',
            'status' => 'open',
            'equipment_type_snapshot' => [
                'id' => $desktopEquipment->id,
                'name' => $desktopEquipment->name,
                'description' => $desktopEquipment->description,
            ],
        ]);

        $executionService = app(AnalysisExecutionService::class);

        $analysisNotebook = $executionService->createAnalysisServiceInstance(
            $orderNotebook,
            $notebookType->id,
            null,
            'Analise inicial de notebook para demonstracao.'
        );

        $executionService->createAnalysisServiceInstance(
            $orderComputer,
            $computerType->id,
            null,
            'Analise inicial de computador para demonstracao.'
        );

        $analysisNotebook->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }
}

