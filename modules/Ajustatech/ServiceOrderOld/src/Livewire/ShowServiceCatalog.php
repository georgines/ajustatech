<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\ServiceOrderOld\Database\Models\AnalysisType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowServiceCatalog extends Component
{
    public string $title = 'Tipos de Analise cadastrados';

    public function toggleStatus(string $id): void
    {
        $analysisType = AnalysisType::query()->findOrFail($id);
        $analysisType->update([
            'is_active' => !$analysisType->is_active,
        ]);
    }

    public function render()
    {
        $services = AnalysisType::query()
            ->withCount('sections')
            ->with(['sections.questions'])
            ->orderBy('name')
            ->get();

        return view('service-order::livewire.show-service-catalog', [
            'services' => $services,
        ]);
    }
}
