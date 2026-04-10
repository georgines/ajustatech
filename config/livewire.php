<?php

$config = require base_path('vendor/livewire/livewire/config/livewire.php');

$previewMimes = (array) data_get($config, 'temporary_file_upload.preview_mimes', []);

if (! in_array('pdf', $previewMimes, true)) {
    $previewMimes[] = 'pdf';
}

$config['temporary_file_upload']['preview_mimes'] = $previewMimes;

return $config;

