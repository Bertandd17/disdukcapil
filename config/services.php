<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | EasyOCR Service Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk layanan OCR KTP menggunakan EasyOCR (Python).
    | EasyOCR adalah library OCR gratis berbasis PyTorch.
    |
    */

    'easyocr' => [
        // Gunakan Flask API mode (true) atau CLI mode (false)
        // API mode membutuhkan Flask server berjalan
        'use_api' => env('EASYOCR_USE_API', false),
        
        // Flask API host dan port
        'api_url' => env('EASYOCR_API_URL'),
        'api_host' => env('EASYOCR_API_HOST', '127.0.0.1'),
        'api_port' => env('EASYOCR_API_PORT', 5000),
        'api_health_check' => env('EASYOCR_API_HEALTH_CHECK', false),
        'api_health_timeout' => env('EASYOCR_API_HEALTH_TIMEOUT', 15),
        
        // Path ke Python executable (untuk CLI mode)
        'python_path' => env('EASYOCR_PYTHON_PATH', 'python'),
        
        // Path ke script Python OCR
        'script_path' => env('EASYOCR_SCRIPT_PATH', base_path('scripts/easyocr_ktp.py')),

        // Jalankan Python CLI lokal. Di production Railway default dimatikan karena container PHP
        // tidak membawa runtime Python + model ML besar. Untuk Railway gunakan OCR.space.
        'cli_enabled' => env('EASYOCR_CLI_ENABLED', env('APP_ENV') !== 'production'),
        
        // Timeout untuk proses OCR (dalam detik)
        'timeout' => env('EASYOCR_TIMEOUT', 300),
        
        // GPU mode (jika tersedia)
        'use_gpu' => env('EASYOCR_USE_GPU', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | OCR.space Service Configuration
    |--------------------------------------------------------------------------
    |
    | Provider OCR online untuk membaca teks KTP sebelum fallback ke EasyOCR.
    |
    */

    'ocr_space' => [
        'enabled' => env('OCR_SPACE_ENABLED', false),
        'api_key' => env('OCR_SPACE_API_KEY'),
        'endpoint' => env('OCR_SPACE_ENDPOINT', 'https://api.ocr.space/parse/image'),
        'language' => env('OCR_SPACE_LANGUAGE', 'auto'),
        'engine' => env('OCR_SPACE_ENGINE', '2'),
        'timeout' => env('OCR_SPACE_TIMEOUT', 60),
        'max_file_size' => env('OCR_SPACE_MAX_FILE_SIZE', 1200000),
        'max_dimension' => env('OCR_SPACE_MAX_DIMENSION', 1280),
    ],

    /*
    |--------------------------------------------------------------------------
    | KTP OCR Model Services
    |--------------------------------------------------------------------------
    |
    | Empat model OCR lokal yang dipakai service Python. Path ini dapat dioverride
    | lewat Railway variables jika model dipasang pada service OCR terpisah.
    |
    */

    'ktp_ocr_models' => [
        'base_path' => env('KTP_OCR_MODELS_PATH', base_path('scripts/models')),
        'crnn_state_dict' => env('KTP_CRNN_STATE_DICT_PATH', base_path('scripts/models/ktp_crnn_v2_state_dict.pt')),
        'crnn_traced' => env('KTP_CRNN_TRACED_PATH', base_path('scripts/models/ktp_crnn_v2_traced.pt')),
        'easyocr_model_dir' => env('EASYOCR_MODEL_DIR', base_path('scripts/models/easyocr_models')),
        'easyocr_detector' => env('EASYOCR_DETECTOR_MODEL_PATH', base_path('scripts/models/easyocr_models/craft_mlt_25k.pth')),
        'easyocr_recognizer' => env('EASYOCR_RECOGNIZER_MODEL_PATH', base_path('scripts/models/easyocr_models/latin_g2.pth')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Vision Service Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi alternatif untuk Google Cloud Vision API.
    | Jika EasyOCR tidak tersedia, sistem akan fallback ke Google Vision.
    |
    */

    'google_vision' => [
        'api_key' => env('GOOGLE_VISION_API_KEY'),
        'credentials_path' => env('GOOGLE_VISION_CREDENTIALS_PATH', 'storage/app/google-creds.json'),
        'mock_dataset_dir' => env('GOOGLE_VISION_MOCK_DIR', base_path('model/dataset/Test')),
        'timeout' => env('GOOGLE_VISION_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | GCP KTP OCR Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Cloud Function GCP (serverless OCR).
    |
    */

    'gcp_ktp' => [
        'mock_enabled' => env('GCP_MOCK_ENABLED', true),
        'mock_delay_seconds' => env('GCP_MOCK_DELAY_SECONDS', 2),
        'mock_dataset_dir' => env('GCP_MOCK_DATASET_DIR', base_path('model/dataset/Test')),
        'webhook_secret' => env('GCP_WEBHOOK_SECRET', ''),
        'vision_credentials_path' => env('GOOGLE_APPLICATION_CREDENTIALS', ''),
    ],

];
