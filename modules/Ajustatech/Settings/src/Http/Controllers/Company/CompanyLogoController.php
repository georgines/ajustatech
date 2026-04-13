<?php

namespace Ajustatech\Settings\Http\Controllers\Company;

use Ajustatech\Core\Traits\HandlesFileUploads;
use Ajustatech\Settings\Database\Models\Company\CompanySetting;
use Symfony\Component\HttpFoundation\Response;

class CompanyLogoController
{
    use HandlesFileUploads;

    public function __invoke(): Response
    {
        $setting = CompanySetting::singleton();

        if (! $setting->hasLogo()) {
            abort(404);
        }

        return $this->streamUploadedFile(
            disk: (string) $setting->logo_disk,
            path: (string) $setting->logo_path,
            mimeType: $setting->logo_mime_type
        );
    }
}
