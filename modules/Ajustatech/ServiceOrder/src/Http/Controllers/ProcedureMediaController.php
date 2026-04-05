<?php

namespace Ajustatech\ServiceOrder\Http\Controllers;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\ServiceOrder\Database\Models\Procedure\ServiceOrderProcedureMedia;
use Symfony\Component\HttpFoundation\Response;

class ProcedureMediaController
{
    use HandlesFileUploads;

    public function __invoke(string $id): Response
    {
        $media = ServiceOrderProcedureMedia::findOrFailById($id);

        if (!$media->disk || !$media->path) {
            abort(404);
        }

        return $this->streamUploadedFile(
            disk: (string) $media->disk,
            path: (string) $media->path,
            mimeType: $media->mime_type
        );
    }
}
