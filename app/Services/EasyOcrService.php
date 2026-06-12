<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * EasyOcrService - Service untuk OCR KTP menggunakan EasyOCR
 * 
 * EasyOCR adalah library Python yang menggunakan PyTorch untuk OCR.
 * Service ini berkomunikasi dengan Python script Flask API yang menjalankan EasyOCR.
 * 
 * Arsitektur:
 * 1. Upload gambar ke storage sementara
 * 2. Kirim ke Flask API (CLI mode atau API mode)
 * 3. Parse hasil JSON
 * 4. Kembalikan hasil ke controller
 * 
 * Mode:
 * - API Mode (default): Hubungi http://localhost:5000/api/ocr/ktp
 * - CLI Mode: Langsung jalankan python script
 */
class EasyOcrService
{
    private const CACHE_PREFIX = 'easyocr_result:';
    private const CACHE_TTL_MINUTES = 30;
    private const MAX_PROCESS_TIME_SECONDS = 120;
    
    // Flask API defaults
    private const DEFAULT_API_HOST = '127.0.0.1';
    private const DEFAULT_API_PORT = 5000;

    /**
     * Result dari proses OCR
     */
    private array $lastResult = [];

    public function __construct(
        private readonly OcrSpaceService $ocrSpaceService
    ) {
    }

    /**
     * Diagnostic check untuk OCR system
     *
     * @return array{
     *   python_found: bool,
     *   python_version: string|null,
     *   script_exists: bool,
     *   api_mode: bool,
     *   test_result: array|null,
     * }
     */
    public function diagnose(): array
    {
        $apiMode = $this->isApiMode();
        $apiBaseUrl = $apiMode ? $this->getApiBaseUrl() : null;
        $apiReachable = $apiMode ? $this->isApiReachable() : false;
        $cliEnabled = $this->isCliModeEnabled();
        $pythonPath = $this->findPythonPath();
        $scriptPath = base_path('scripts/easyocr_ktp.py');
        
        $pythonVersion = null;
        if ($pythonPath) {
            exec(escapeshellarg($pythonPath) . ' --version 2>&1', $versionOutput, $versionReturn);
            $pythonVersion = implode(' ', $versionOutput);
        }
        
        // Test command execution
        $testResult = null;
        if ($pythonPath && file_exists($scriptPath)) {
            $testCommand = sprintf(
                '"%s" "%s" --diagnose 2>&1',
                $pythonPath,
                $scriptPath
            );
            $testOutput = shell_exec($testCommand);
            $testResult = [
                'output_length' => strlen($testOutput ?? ''),
                'output_preview' => substr($testOutput ?? '', 0, 500),
            ];
        }
        
        return [
            'python_found' => $pythonPath !== null,
            'python_path' => $pythonPath,
            'python_version' => $pythonVersion,
            'script_exists' => file_exists($scriptPath),
            'script_path' => $scriptPath,
            'api_mode' => $apiMode,
            'api_base_url' => $apiBaseUrl,
            'api_reachable' => $apiReachable,
            'cli_enabled' => $cliEnabled,
            'provider' => $apiMode ? 'easyocr_api' : ($cliEnabled ? 'easyocr_cli' : 'not_configured'),
            'test_result' => $testResult,
        ];
    }

    /**
     * Process gambar KTP dengan EasyOCR
     *
     * @param  UploadedFile  $file  File gambar KTP
     * @param  string|null  $antrianId  ID antrian (opsional)
     * @return array{success: bool, data?: array, message: string, raw_text?: string, processing_time?: float}
     */
    public function processKtpImage(UploadedFile $file, ?string $antrianId = null): array
    {
        $startTime = microtime(true);
        $tempPath = null;
        $ocrSpaceError = null;
        
        try {
            // 1. Validasi file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                ];
            }

            // 2. Simpan file sementara
            $tempPath = $this->storeTempFile($file);
            
            Log::info('EasyOcrService: Processing image with EasyOCR', [
                'temp_path' => $tempPath,
                'file_size' => $file->getSize(),
            ]);

            // 3. Gunakan OCR.space sebagai prioritas jika dikonfigurasi
            if ($this->ocrSpaceService->isEnabled()) {
                $ocrSpaceResult = $this->ocrSpaceService->processKtpImage($tempPath);

                if ($ocrSpaceResult['success'] ?? false) {
                    $processingTime = round(microtime(true) - $startTime, 2);
                    $this->lastResult = $ocrSpaceResult;

                    Log::info('EasyOcrService: OCR completed via OCR.space', [
                        'processing_time' => $processingTime,
                        'has_data' => !empty($ocrSpaceResult['data']),
                        'data_keys' => array_keys($ocrSpaceResult['data'] ?? []),
                        'confidence' => $ocrSpaceResult['confidence'] ?? 0,
                    ]);

                    return [
                        'success' => true,
                        'data' => $ocrSpaceResult['data'],
                        'raw_text' => $ocrSpaceResult['raw_text'] ?? '',
                        'confidence' => $ocrSpaceResult['confidence'] ?? 0,
                        'field_confidence' => $ocrSpaceResult['field_confidence'] ?? [],
                        'ocr_source' => 'ocr_space',
                        'ocr_meta' => $ocrSpaceResult['ocr_meta'] ?? [],
                        'message' => 'OCR berhasil diproses dengan OCR.space',
                        'processing_time' => $processingTime,
                    ];
                }

                Log::warning('EasyOcrService: OCR.space failed, falling back to EasyOCR', [
                    'error' => $ocrSpaceResult['error'] ?? 'Unknown OCR.space error',
                ]);

                $ocrSpaceError = $ocrSpaceResult['error'] ?? 'Provider OCR.space gagal memproses gambar.';
            }

            // 4. Jalankan EasyOCR Python script
            $ocrResult = $this->runEasyOcr($tempPath);
            
            // 5. Parse hasil
            $this->lastResult = $ocrResult;
            
            $processingTime = round(microtime(true) - $startTime, 2);
            
            Log::info('EasyOcrService: OCR completed', [
                'success' => $ocrResult['success'],
                'processing_time' => $processingTime,
                'has_data' => !empty($ocrResult['data']),
                'data_keys' => array_keys($ocrResult['data'] ?? []),
                'confidence' => $ocrResult['confidence'] ?? 0,
            ]);
            
            if ($ocrResult['success']) {
                return [
                    'success' => true,
                    'data' => $ocrResult['data'],
                    'raw_text' => $ocrResult['raw_text'] ?? '',
                    'confidence' => $ocrResult['confidence'] ?? 0,
                    'field_confidence' => $ocrResult['field_confidence'] ?? [],
                    'ocr_source' => $ocrResult['ocr_source'] ?? null,
                    'ocr_meta' => $ocrResult['ocr_meta'] ?? [],
                    'message' => 'OCR berhasil diproses',
                    'processing_time' => $processingTime,
                ];
            }
            
            $message = $ocrResult['error'] ?? 'Gagal memproses OCR dengan EasyOCR';
            if ($ocrSpaceError !== null) {
                $message = 'OCR.space gagal: ' . $ocrSpaceError . ' Fallback EasyOCR: ' . $message;
            }

            return [
                'success' => false,
                'message' => $message,
                'processing_time' => $processingTime,
            ];

        } catch (\Throwable $e) {
            Log::error('EasyOcrService: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan EasyOCR: ' . $e->getMessage(),
                'processing_time' => round(microtime(true) - $startTime, 2),
            ];
        } finally {
            // 5. Cleanup file sementara
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Process multiple images
     *
     * @param  array<UploadedFile>  $files
     * @return array{success: bool, results: array, total_processed: int, failed: int}
     */
    public function processMultipleImages(array $files): array
    {
        $results = [];
        $totalProcessed = 0;
        $failed = 0;

        foreach ($files as $index => $file) {
            $result = $this->processKtpImage($file);
            $results[$index] = $result;
            
            if ($result['success']) {
                $totalProcessed++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $failed === 0,
            'results' => $results,
            'total_processed' => $totalProcessed,
            'failed' => $failed,
        ];
    }

    /**
     * Get cached result by antrian ID
     *
     * @param  string  $antrianId
     * @return array|null
     */
    public function getCachedResult(string $antrianId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $antrianId);
    }

    /**
     * Get last processing result
     *
     * @return array
     */
    public function getLastResult(): array
    {
        return $this->lastResult;
    }

    /**
     * Validate uploaded file
     *
     * @param  UploadedFile  $file
     * @return array{valid: bool, message: string}
     */
    private function validateFile(UploadedFile $file): array
    {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/jpeg2000'];
        
        if ($file->getSize() > $maxSize) {
            return [
                'valid' => false,
                'message' => 'Ukuran file maksimal 10MB',
            ];
        }

        $mime = strtolower($file->getMimeType() ?? '');
        if (!in_array($mime, $allowedMimes, true)) {
            return [
                'valid' => false,
                'message' => 'Format file harus JPEG, PNG, atau JPG',
            ];
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Store file temporarily
     *
     * @param  UploadedFile  $file
     * @return string Path ke file sementara
     */
    private function storeTempFile(UploadedFile $file): string
    {
        $tempDir = storage_path('app/temp/ocr');
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $tempPath = $tempDir . '/' . $filename;
        
        // Copy file to temp location
        file_put_contents($tempPath, file_get_contents($file->getRealPath()));
        
        return $tempPath;
    }

    /**
     * Run EasyOCR - supports both API mode and CLI mode
     * Selalu menggunakan EasyOCR real (tanpa mock)
     *
     * @param  string  $imagePath
     * @return array{success: bool, raw_text?: string, data?: array, confidence?: float, error?: string}
     */
    private function runEasyOcr(string $imagePath): array
    {
        // Coba API mode dulu (Flask API) jika diaktifkan
        if ($this->isApiMode()) {
            $apiResult = $this->runViaApi($imagePath);
            if ($apiResult !== null) {
                return $apiResult;
            }
            Log::warning('EasyOcrService: API mode failed, falling back to CLI mode');
        }

        if (!$this->isCliModeEnabled()) {
            return [
                'success' => false,
                'error' => 'Service OCR online belum siap atau tidak bisa dihubungi. Periksa EASYOCR_API_URL dan status service EasyOCR.',
            ];
        }

        // CLI mode - langsung jalankan Python script
        return $this->runViaCli($imagePath);
    }
    
    /**
     * Check if API mode is enabled
     *
     * @return bool
     */
    private function isApiMode(): bool
    {
        return config('services.easyocr.use_api', env('EASYOCR_USE_API', false));
    }

    private function isCliModeEnabled(): bool
    {
        return (bool) config('services.easyocr.cli_enabled', !app()->environment('production'));
    }

    private function getApiBaseUrl(): string
    {
        $apiUrl = trim((string) config('services.easyocr.api_url', ''));
        if ($apiUrl !== '') {
            return rtrim($apiUrl, '/');
        }

        $host = config('services.easyocr.api_host', env('EASYOCR_API_HOST', self::DEFAULT_API_HOST));
        $port = config('services.easyocr.api_port', env('EASYOCR_API_PORT', self::DEFAULT_API_PORT));

        return "http://{$host}:{$port}";
    }

    private function isApiReachable(): bool
    {
        try {
            $timeout = (int) config('services.easyocr.api_health_timeout', 15);

            return Http::timeout($timeout)->get($this->getApiBaseUrl() . '/health')->successful();
        } catch (\Throwable) {
            return false;
        }
    }
    
    /**
     * Run OCR via Flask API
     *
     * @param  string  $imagePath
     * @return array|null Returns null if API is not available
     */
    private function runViaApi(string $imagePath): ?array
    {
        $baseUrl = $this->getApiBaseUrl();
        $timeout = (int) config('services.easyocr.timeout', self::MAX_PROCESS_TIME_SECONDS);
        
        // Check if API is running
        try {
            $healthTimeout = (int) config('services.easyocr.api_health_timeout', 15);
            $healthCheck = Http::timeout($healthTimeout)->get("{$baseUrl}/health");
            if ($healthCheck->failed()) {
                Log::warning('EasyOcrService: API health check failed', [
                    'base_url' => $baseUrl,
                    'status' => $healthCheck->status(),
                ]);
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('EasyOcrService: API not reachable', [
                'base_url' => $baseUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
        
        // Send image to API
        try {
            $response = Http::timeout($timeout)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$baseUrl}/api/ocr/ktp");
            
            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success'] ?? false) {
                    $data = $result['data'] ?? [];
                    return [
                        'success' => true,
                        'raw_text' => $data['_raw_text'] ?? '',
                        'data' => $this->normalizeDataFields($data),
                        'confidence' => $data['_confidence_avg'] ?? 0,
                        'field_confidence' => $data['_field_confidence'] ?? [],
                        'ocr_source' => $data['_ocr_source'] ?? 'easyocr_api',
                        'ocr_meta' => $data['_ocr_meta'] ?? [],
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $result['message'] ?? 'API returned error',
                    ];
                }
            }
            
            Log::error('EasyOcrService: API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('EasyOcrService: API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Normalize data fields from API response to match expected format
     *
     * @param  array  $data
     * @return array
     */
    private function normalizeDataFields(array $data): array
    {
        // Remove internal fields (fields starting with underscore)
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, '_')) {
                unset($data[$key]);
            }
        }
        
        // Standard field names (snake_case as used in Python)
        $fieldMappings = [
            'nik',
            'nama_lengkap',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'gol_darah',
            'alamat',
            'rt_rw',
            'kel_desa',
            'kec',
            'kab_kota',
            'provinsi',
            'agama',
            'status_perkawinan',
            'pekerjaan',
            'kewarganegaraan',
            'berlaku_hingga',
        ];
        
        $normalized = [];
        foreach ($fieldMappings as $key) {
            // Copy field if exists (key is the same in Python and our format)
            if (isset($data[$key])) {
                $normalized[$key] = $data[$key];
            }
        }
        
        // Always ensure all expected fields exist (with empty default)
        foreach ($fieldMappings as $key) {
            if (!isset($normalized[$key])) {
                $normalized[$key] = '';
            }
        }
        
        return $normalized;
    }

    /**
     * Run EasyOCR via CLI (direct Python script execution)
     * Menggunakan EasyOCR real tanpa mock
     *
     * @param  string  $imagePath
     * @return array{success: bool, raw_text?: string, data?: array, confidence?: float, error?: string}
     */
    private function runViaCli(string $imagePath): array
    {
        // Path ke Python script
        $pythonScript = base_path('scripts/easyocr_ktp.py');
        
        Log::info('EasyOcrService: runViaCli started', [
            'image_path' => $imagePath,
            'image_exists' => file_exists($imagePath),
        ]);
        
        if (!file_exists($pythonScript)) {
            Log::error('EasyOcrService: Python script not found', [
                'script_path' => $pythonScript,
            ]);
            return [
                'success' => false,
                'error' => 'Python script easyocr_ktp.py tidak ditemukan di folder scripts/',
            ];
        }

        // Cek Python installation
        $pythonPath = $this->findPythonPath();
        if (!$pythonPath) {
            Log::error('EasyOcrService: Python not found');
            return [
                'success' => false,
                'error' => 'Python tidak ditemukan. Pastikan Python sudah terinstall.',
            ];
        }

        Log::info('EasyOcrService: Executing Python script', [
            'python' => $pythonPath,
            'script' => $pythonScript,
            'image' => $imagePath,
        ]);

        // Build command - Windows compatible
        // Useescapeshellarg for each path to handle spaces correctly
        $command = sprintf(
            '%s %s %s 2>&1',
            escapeshellarg($pythonPath),
            escapeshellarg($pythonScript),
            escapeshellarg($imagePath)
        );

        Log::debug('EasyOcrService: Full command', ['command' => $command]);

        // Execute using shell_exec with proper Windows handling
        $fullOutput = shell_exec($command);

        $outputLength = strlen($fullOutput ?? '');
        Log::info('EasyOcrService: Command output received', [
            'output_length' => $outputLength,
            'output_preview' => substr($fullOutput ?? '', 0, 500),
        ]);

        if (empty($fullOutput)) {
            Log::error('EasyOcrService: No output from Python script');
            return [
                'success' => false,
                'error' => 'Python script tidak memberikan output. Cek instalasi EasyOCR.',
            ];
        }

        // Parse JSON output
        $parsed = $this->parseOcrOutput($fullOutput);
        
        if (!$parsed['success']) {
            Log::warning('EasyOcrService: OCR parsing failed', [
                'error' => $parsed['error'] ?? 'Unknown error',
            ]);
            return [
                'success' => false,
                'error' => 'Gagal parse output EasyOCR: ' . ($parsed['error'] ?? 'Unknown error'),
            ];
        }

        return $parsed;
    }

    /**
     * Parse output dari EasyOCR script
     *
     * @param  string  $output
     * @return array{success: bool, raw_text?: string, data?: array, confidence?: float, error?: string}
     */
    private function parseOcrOutput(string $output): array
    {
        Log::info('EasyOcrService: Parsing OCR output', [
            'output_length' => strlen($output),
        ]);
        
        // Clean output - remove any non-printable characters
        $cleanOutput = trim($output);
        
        // Strategy 1: Try to find JSON object anywhere in the output
        $jsonMatch = [];
        if (preg_match('/\{[\s\S]*\}\s*$/', $cleanOutput, $jsonMatch)) {
            $jsonStr = trim($jsonMatch[0]);
            
            Log::debug('EasyOcrService: Found JSON candidate', ['length' => strlen($jsonStr)]);
            
            try {
                $result = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
                
                if (is_array($result)) {
                    // Handle CLI format: {"status": "success", ...}
                    if (isset($result['status'])) {
                        if ($result['status'] === 'success') {
                            Log::info('EasyOcrService: CLI JSON parsed successfully');
                            return [
                                'success' => true,
                                'raw_text' => $result['raw_text'] ?? $result['raw'] ?? '',
                                'data' => $this->normalizeDataFields($result['data'] ?? []),
                                'confidence' => $result['confidence'] ?? $result['conf'] ?? 0,
                                'field_confidence' => $result['field_confidence'] ?? [],
                                'ocr_source' => $result['ocr_source'] ?? null,
                                'ocr_meta' => $result['ocr_meta'] ?? [],
                            ];
                        } else {
                            return [
                                'success' => false,
                                'error' => $result['error'] ?? 'Unknown error from OCR script',
                            ];
                        }
                    }
                    
                    // Handle API format: {"success": true, ...}
                    if (isset($result['success'])) {
                        if ($result['success']) {
                            Log::info('EasyOcrService: API JSON parsed successfully');
                            return [
                                'success' => true,
                                'raw_text' => $result['data']['_raw_text'] ?? '',
                                'data' => $this->normalizeDataFields($result['data'] ?? []),
                                'confidence' => $result['data']['_confidence_avg'] ?? 0,
                                'field_confidence' => $result['data']['_field_confidence'] ?? [],
                                'ocr_source' => $result['data']['_ocr_source'] ?? null,
                                'ocr_meta' => $result['data']['_ocr_meta'] ?? [],
                            ];
                        } else {
                            return [
                                'success' => false,
                                'error' => $result['message'] ?? 'Unknown error from OCR API',
                            ];
                        }
                    }
                }
            } catch (\JsonException $e) {
                Log::warning('EasyOcrService: JSON decode failed', ['error' => $e->getMessage()]);
            }
        }

        // Strategy 2: Try to find JSON starting from first {
        if (preg_match('/\{[\s\S]+/', $cleanOutput, $jsonMatch)) {
            $start = strpos($cleanOutput, '{');
            $jsonCandidate = substr($cleanOutput, $start);
            
            // Try to find the matching closing brace
            $depth = 0;
            $end = 0;
            for ($i = 0; $i < strlen($jsonCandidate); $i++) {
                if ($jsonCandidate[$i] === '{') {
                    $depth++;
                } elseif ($jsonCandidate[$i] === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $i + 1;
                        break;
                    }
                }
            }
            
            if ($end > 0) {
                $jsonStr = substr($jsonCandidate, 0, $end);
                
                try {
                    $result = json_decode($jsonStr, true, 512, JSON_THROW_ON_ERROR);
                    
                    if (is_array($result) && isset($result['status'])) {
                        if ($result['status'] === 'success') {
                            Log::info('EasyOcrService: CLI JSON parsed (strategy 2)');
                            return [
                                'success' => true,
                                'raw_text' => $result['raw_text'] ?? $result['raw'] ?? '',
                                'data' => $this->normalizeDataFields($result['data'] ?? []),
                                'confidence' => $result['confidence'] ?? $result['conf'] ?? 0,
                                'field_confidence' => $result['field_confidence'] ?? [],
                                'ocr_source' => $result['ocr_source'] ?? null,
                                'ocr_meta' => $result['ocr_meta'] ?? [],
                            ];
                        } else {
                            return [
                                'success' => false,
                                'error' => $result['error'] ?? 'Unknown error from OCR script',
                            ];
                        }
                    }
                } catch (\JsonException $e) {
                    Log::warning('EasyOcrService: JSON decode failed (strategy 2)', ['error' => $e->getMessage()]);
                }
            }
        }

        // Jika tidak ada JSON valid, return error
        Log::warning('EasyOcrService: No valid JSON found in output', [
            'output_length' => strlen($output),
            'output_preview' => substr($cleanOutput, 0, 300),
        ]);
        
        return [
            'success' => false,
            'error' => 'Output OCR tidak valid atau kosong. Pastikan EasyOCR sudah terinstall dengan benar.',
        ];
    }

    /**
     * Find Python executable path
     *
     * @return string|null
     */
    private function findPythonPath(): ?string
    {
        $paths = [
            'python',
            'python3',
            'C:\Python312\python.exe',
            'C:\Python311\python.exe',
            'C:\Python310\python.exe',
            'C:\Python39\python.exe',
            '/usr/bin/python3',
            '/usr/local/bin/python3',
        ];

        foreach ($paths as $path) {
            $result = [];
            exec(escapeshellarg($path) . ' --version 2>&1', $result, $returnCode);
            
            if ($returnCode === 0) {
                Log::info('EasyOcrService: Found Python', ['path' => $path, 'version' => implode(' ', $result)]);
                return $path;
            }
        }

        return null;
    }

    /**
     * Cache result for antrian
     *
     * @param  string  $antrianId
     * @param  array  $result
     * @return void
     */
    private function cacheResult(string $antrianId, array $result): void
    {
        Cache::put(
            self::CACHE_PREFIX . $antrianId,
            [
                'data' => $result['data'] ?? [],
                'raw_text' => $result['raw_text'] ?? '',
                'confidence' => $result['confidence'] ?? 0,
                'processed_at' => now()->toIso8601String(),
            ],
            now()->addMinutes(self::CACHE_TTL_MINUTES)
        );
    }
}
