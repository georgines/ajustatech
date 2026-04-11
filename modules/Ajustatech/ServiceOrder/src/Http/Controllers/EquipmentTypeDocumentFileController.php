<?php

namespace Ajustatech\ServiceOrder\Http\Controllers;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType\ServiceOrderEquipmentTypeDocument;
use Symfony\Component\HttpFoundation\Response;

class EquipmentTypeDocumentFileController
{
    use HandlesFileUploads;

    public function __invoke(string $id): Response
    {
        $document = ServiceOrderEquipmentTypeDocument::query()->findOrFail($id);

        if (! $document->disk || ! $document->path) {
            abort(404);
        }

        return $this->streamUploadedFile(
            disk: (string) $document->disk,
            path: (string) $document->path,
            mimeType: $document->mime_type
        );
    }
}

