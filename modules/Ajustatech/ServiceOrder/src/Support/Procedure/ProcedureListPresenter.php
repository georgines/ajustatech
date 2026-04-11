<?php

namespace Ajustatech\ServiceOrder\Support\Procedure;

use Illuminate\Database\Eloquent\Collection;

class ProcedureListPresenter
{
    public function map(Collection $procedures): array
    {
        return $procedures
            ->map(fn ($procedure) => $this->mapProcedure($procedure))
            ->all();
    }

    private function mapProcedure(object $procedure): array
    {
        $images = $this->mapImageMedia($procedure);
        $videos = $this->mapVideoMedia($procedure);
        $pdfs = $this->mapPdfMedia($procedure);

        $this->appendLegacyHelpMedia($procedure, $images, $videos);

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
    }

    private function mapImageMedia(object $procedure): array
    {
        return $procedure->media
            ->where('type', 'image')
            ->map(fn ($media) => [
                'url' => $media->path ? route('service-order-procedures-media-file', ['id' => $media->id]) : null,
                'description' => $media->description,
            ])
            ->values()
            ->all();
    }

    private function mapVideoMedia(object $procedure): array
    {
        return $procedure->media
            ->where('type', 'video')
            ->map(fn ($media) => [
                'url' => $media->url,
                'description' => $media->description,
            ])
            ->values()
            ->all();
    }

    private function mapPdfMedia(object $procedure): array
    {
        return $procedure->media
            ->where('type', 'pdf')
            ->map(fn ($media) => [
                'url' => $media->path ? route('service-order-procedures-media-file', ['id' => $media->id]) : null,
                'description' => $media->description,
                'name' => $media->display_name ?: 'PDF',
            ])
            ->values()
            ->all();
    }

    private function appendLegacyHelpMedia(object $procedure, array &$images, array &$videos): void
    {
        if (!empty($procedure->help_image_url)) {
            $images[] = [
                'url' => $procedure->help_image_url,
                'description' => null,
            ];
        }

        if (empty($procedure->help_video_url)) {
            return;
        }

        $videos[] = [
            'url' => $procedure->help_video_url,
            'description' => null,
        ];
    }
}
