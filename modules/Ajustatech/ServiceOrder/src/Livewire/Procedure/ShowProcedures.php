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
            ->map(function ($procedure) {
                $images = $procedure->media
                    ->where('type', 'image')
                    ->map(fn ($media) => [
                        'url' => $media->path ? route('service-order-procedures-media-file', ['id' => $media->id]) : null,
                        'description' => $media->description,
                    ])
                    ->values()
                    ->all();
                $videos = $procedure->media
                    ->where('type', 'video')
                    ->map(fn ($media) => [
                        'url' => $media->url,
                        'description' => $media->description,
                    ])
                    ->values()
                    ->all();
                $pdfs = $procedure->media
                    ->where('type', 'pdf')
                    ->map(fn ($media) => [
                        'url' => $media->path ? route('service-order-procedures-media-file', ['id' => $media->id]) : null,
                        'description' => $media->description,
                        'name' => $media->original_name ?: 'PDF',
                    ])
                    ->values()
                    ->all();

                if (!empty($procedure->help_image_url)) {
                    $images[] = [
                        'url' => $procedure->help_image_url,
                        'description' => null,
                    ];
                }

                if (!empty($procedure->help_video_url)) {
                    $videos[] = [
                        'url' => $procedure->help_video_url,
                        'description' => null,
                    ];
                }

                return [
                    'id' => $procedure->id,
                    'name' => $procedure->name,
                    'description' => $procedure->description,
                    'value' => (float) $procedure->value,
                    'help_text' => $procedure->help_text,
                    'images' => $images,
                    'videos' => $videos,
                    'pdfs' => $pdfs,
                    'has_help' => !empty($procedure->help_text)
                        || !empty($images)
                        || !empty($videos)
                        || !empty($pdfs),
                ];
            })
            ->all();
    }

    public function render()
    {
        return view('service-order::livewire.procedure.show-procedures');
    }
}
