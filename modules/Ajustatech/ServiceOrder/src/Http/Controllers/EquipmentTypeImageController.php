<?php

namespace Ajustatech\ServiceOrder\Http\Controllers;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\ServiceOrder\Database\Models\EquipmentType;
use Symfony\Component\HttpFoundation\Response;

class EquipmentTypeImageController
{
    use HandlesFileUploads;

    public function __invoke(string $id): Response
    {
        $equipmentType = EquipmentType::findOrFailById($id);

        if (!$equipmentType->hasImage()) {
            abort(404);
        }

        $disk = (string) $equipmentType->image_disk;
        $path = (string) $equipmentType->image_path;

        return $this->streamUploadedFile(
            disk: $disk,
            path: $path,
            mimeType: $equipmentType->image_mime_type
        );
    }
}
