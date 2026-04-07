<?php

namespace Ajustatech\ServiceOrder\Livewire\Analysis;

use Ajustatech\ServiceOrder\Database\Models\Analysis\ServiceOrderAnalysisService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowAnalysisServices extends Component
{
    public string $title = '';

    public array $analysisServices = [];

    public function mount(): void
    {
        $this->title = trans('service-order::messages.analysis_services_title');
        $this->analysisServices = $this->mapRows();
    }

    public function deleteAnalysisService(string $id): void
    {
        ServiceOrderAnalysisService::deleteById($id);
        $this->analysisServices = array_values(array_filter(
            $this->analysisServices,
            fn (array $item) => ($item['id'] ?? null) !== $id
        ));
    }

    private function mapRows(): array
    {
        return ServiceOrderAnalysisService::listForIndex()
            ->map(fn ($analysis) => [
                'id' => $analysis->id,
                'name' => $analysis->name,
                'description' => $analysis->description,
                'value' => (float) $analysis->value,
                'questions_count' => (int) ($analysis->questions_count ?? 0),
            ])
            ->all();
    }

    public function render()
    {
        return view('service-order::livewire.analysis.show-analysis-services');
    }
}
