<?php

return [
    'equipment_type_images' => [
        'disk' => env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_DISK', env('FILESYSTEM_DISK', 'public')),
        'directory' => env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_DIRECTORY', 'equipment-types'),
        'visibility' => env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_VISIBILITY', 'private'),
        'max_size_kb' => (int) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_MAX_SIZE_KB', 5120),
        'allowed_extensions' => array_values(array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            explode(',', (string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,webp'))
        ))),
        'allowed_mime_types' => array_values(array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            explode(',', (string) env('SERVICE_ORDER_EQUIPMENT_TYPE_IMAGE_ALLOWED_MIME_TYPES', 'image/jpeg,image/png,image/webp'))
        ))),
    ],
];
