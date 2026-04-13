<?php

return [
    'company' => [
        'logo' => [
            'disk' => env('SETTINGS_COMPANY_LOGO_DISK', 'public'),
            'directory' => env('SETTINGS_COMPANY_LOGO_DIRECTORY', 'settings/company/logo'),
            'visibility' => env('SETTINGS_COMPANY_LOGO_VISIBILITY', 'public'),
            'max_size_kb' => (int) env('SETTINGS_COMPANY_LOGO_MAX_SIZE_KB', 5120),
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
        ],
    ],
];
