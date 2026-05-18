<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi keamanan untuk aplikasi Sistem Informasi Disdukcapil
    | Kabupaten Toba.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'enabled' => env('CSP_ENABLED', false),
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https://cdn.tailwindcss.com", "https://cdn.jsdelivr.net"],
            'style-src' => ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com", "https://cdn.tailwindcss.com"],
            'img-src' => ["'self'", "data:", "https:", "blob:"],
            'font-src' => ["'self'", "https://fonts.gstatic.com", "https://cdnjs.cloudflare.com"],
            'connect-src' => ["'self'", env('OCR_API_URL', 'http://127.0.0.1:8000')],
            'frame-src' => ["'none'"],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'upgrade-insecure-requests' => [],
        ],
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri' => env('CSP_REPORT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x-frame-options' => 'DENY',
        'x-content-type-options' => 'nosniff',
        'x-xss-protection' => '1; mode=block',
        'strict-transport-security' => 'max-age=31536000; includeSubDomains; preload',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'permissions-policy' => 'geolocation=(), microphone=(), camera=(self)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),

        // General API rate limiting
        'api' => [
            'max_requests' => env('API_RATE_LIMIT', 60),
            'decay_minutes' => env('API_RATE_DECAY', 1),
        ],

        // Authentication endpoints
        'auth' => [
            'max_requests' => env('AUTH_RATE_LIMIT', 5),
            'decay_minutes' => env('AUTH_RATE_DECAY', 1),
        ],

        // File upload endpoints
        'upload' => [
            'max_requests' => env('UPLOAD_RATE_LIMIT', 10),
            'decay_minutes' => env('UPLOAD_RATE_DECAY', 5),
        ],

        // OCR endpoints
        'ocr' => [
            'max_requests' => env('OCR_RATE_LIMIT', 20),
            'decay_minutes' => env('OCR_RATE_DECAY', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection
    |--------------------------------------------------------------------------
    */
    'xss' => [
        'enabled' => true,
        'purifier' => [
            'enabled' => env('XSS_PURIFIER_ENABLED', true),
            'encoding' => 'UTF-8',
            'html_allowed' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'enabled' => true,
        'token_length' => 32,
        'exclude_routes' => [
            // Add routes to exclude from CSRF protection
            // 'api/external-webhook',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'max_size' => env('MAX_UPLOAD_SIZE', 5242880), // 5MB in bytes
        'allowed_mime_types' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf',
        ],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
        'randomize_names' => true,
        'scan_uploads' => env('SCAN_UPLOADS', false),
        'storage_disk' => env('FILE_UPLOAD_DISK', 'secure'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'expire_on_close' => true,
        'secure' => env('SESSION_SECURE', true),
        'http_only' => true,
        'same_site' => 'strict',
        'max_concurrent_sessions' => env('MAX_CONCURRENT_SESSIONS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special_char' => true,
        'hash_algorithm' => 'argon2id',
        'hash_options' => [
            'memory' => 65536,
            'time_cost' => 4,
            'threads' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist/Blacklist
    |--------------------------------------------------------------------------
    */
    'ip' => [
        'admin_whitelist' => array_filter(explode(',', env('ADMIN_IP_WHITELIST', ''))),
        'blacklist' => array_filter(explode(',', env('IP_BLACKLIST', ''))),
        'enable_ip_check' => env('ENABLE_IP_CHECK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_auth_events' => true,
        'log_data_changes' => true,
        'log_file_access' => true,
        'log_failed_attempts' => true,
        'retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'pii_fields' => [
            'nik',
            'nomor_kk',
            'nomor_akta',
        ],
        'algorithm' => 'AES-256-CBC',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocking Rules
    |--------------------------------------------------------------------------
    */
    'blocking' => [
        'max_failed_attempts' => env('MAX_FAILED_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION_MINUTES', 15),
        'enable_device_fingerprinting' => env('ENABLE_DEVICE_FINGERPRINTING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        'alert_email' => env('SECURITY_ALERT_EMAIL'),
        'alert_on_suspicious_activity' => true,
        'suspicious_threshold' => 10, // attempts per minute
    ],
];
