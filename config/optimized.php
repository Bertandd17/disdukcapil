<?php

return [
    'max_execution_time' => env('MAX_EXECUTION_TIME', 300), // 5 minutes
    'max_input_time' => env('MAX_INPUT_TIME', 300),
    'memory_limit' => env('MEMORY_LIMIT', '512M'),
    'post_max_size' => env('POST_MAX_SIZE', '50M'),
    'upload_max_filesize' => env('UPLOAD_MAX_FILESIZE', '50M'),
];
