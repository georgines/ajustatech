<?php

namespace Ajustatech\ServiceOrderOld\Livewire;

use Ajustatech\ServiceOrderOld\Database\Models\ServiceOrderAnalysisService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowPendingAnalysisServices extends Component
{
    public string $title = 'Analises a Realizar';

    public function render()
    {
        $analysisServices = ServiceOrderAnalysisService::query()
            ->with(['order', 'analysisType'])
            ->withCount([
                'questions as total_questions_count' => fn ($query) => $query->where('is_active', true),
                'responses as answered_questions_count',
            ])
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->get();

        return view('service-order::livewire.show-pending-analysis-services', [
            'analysisServices' => $analysisServices,
            'statusLabels' => [
                'pending' => 'Pendente',
                'in_progress' => 'Em andamento',
                'finalized' => 'Finalizado',
                'reviewed' => 'Revisado',
            ],
        ]);
    }
}
