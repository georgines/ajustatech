<?php

namespace Ajustatech\ServiceOrder\Livewire\Procedure;

use Ajustatech\Core\Traits\SwitchAlertDispatch;
use Ajustatech\ServiceOrder\Services\Procedure\Contracts\ProcedureServiceInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('core::layouts.app')]
class ShowProcedures extends Component
{
    use SwitchAlertDispatch;

    public $title;
    public $procedures = [];
    public $selectedHelp = null;

    public function mount(ProcedureServiceInterface $service): void
    {
        $this->title = trans('service-order::messages.procedures_title');
        $this->procedures = $service->listProcedures();
    }

    public function confirmDelete(string $id): void
    {
        $this->dispatchConfirmation(trans('service-order::messages.procedure_confirm_delete'))
            ->to('delete-procedure', id: $id)
            ->typeWarning()
            ->setButtonOK(trans('service-order::messages.confirm_yes'))
            ->setButtonCancel(trans('service-order::messages.confirm_no'))
            ->run();
    }

    #[On('delete-procedure')]
    public function deleteProcedure(string $id, ProcedureServiceInterface $service): void
    {
        $service->deleteProcedure($id);
        $this->procedures = $service->listProcedures();
    }

    public function showHelp(string $id, ProcedureServiceInterface $service): void
    {
        $procedure = $service->findProcedure($id);
        $this->selectedHelp = $procedure;
    }

    public function getVideoEmbedUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $parsed = parse_url($url);
        $host = strtolower((string) ($parsed['host'] ?? ''));

        if (str_contains($host, 'youtu.be')) {
            $videoId = trim((string) ($parsed['path'] ?? ''), '/');

            return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str((string) ($parsed['query'] ?? ''), $query);
            $videoId = (string) ($query['v'] ?? '');

            return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
        }

        return $url;
    }

    public function render()
    {
        return view('service-order::livewire.procedure.show-procedures');
    }
}
