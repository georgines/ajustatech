<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Ajustatech\ServiceOrder\Support\Procedure\ProcedureListPresenter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowProcedures extends Component
{
    public $title;
    public $procedures = [];

    public function mount(ProcedureServiceInterface $service, ProcedureListPresenter $presenter): void
    {
        $this->title = trans('service-order::messages.procedures_title');
        $this->procedures = $this->mapProcedures($service, $presenter);
    }

    public function deleteProcedure(string $id, ProcedureServiceInterface $service, ProcedureListPresenter $presenter): void
    {
        $service->deleteProcedure($id);
        $this->procedures = $this->mapProcedures($service, $presenter);
    }

    private function mapProcedures(ProcedureServiceInterface $service, ProcedureListPresenter $presenter): array
    {
        $procedures = $service->listProcedures();

        return $presenter->map($procedures);
    }

    public function render()
    {
        return view('service-order::livewire.procedure.show-procedures');
    }
}
