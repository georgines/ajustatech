<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowProcedures extends Component
{
    public $title;
    public $procedures = [];

    public function mount(ProcedureServiceInterface $service): void
    {
        $this->title = trans('service-order::messages.procedures_title');
        $this->procedures = $this->mapProcedures($service);
    }

    public function deleteProcedure(string $id, ProcedureServiceInterface $service): void
    {
        $service->deleteProcedure($id);
        $this->procedures = $this->mapProcedures($service);
    }

    private function mapProcedures(ProcedureServiceInterface $service): array
    {
        return $service->listProcedures()
            ->map(fn ($procedure) => [
                'id' => $procedure->id,
                'name' => $procedure->name,
                'description' => $procedure->description,
                'value' => (float) $procedure->value,
                'help_text' => $procedure->help_text,
                'help_image_url' => $procedure->help_image_url,
                'help_video_url' => $procedure->help_video_url,
                'has_help' => !empty($procedure->help_text)
                    || !empty($procedure->help_image_url)
                    || !empty($procedure->help_video_url),
            ])
            ->all();
    }

    public function render()
    {
        return view('service-order::livewire.procedure.show-procedures');
    }
}
