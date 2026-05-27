<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SecureFileUploadService — Security hardening untuk file upload.
 *
 * Melakukan validasi berlapis:
 *  1. MIME type server-side (finfo_file / mime_content_type)
 *  2. Ekstensi file (jpg/png/pdf case-insensitive)
 *  3. Ukuran file (max 2MB / 2048 KB)
 *  4. Path traversal scan (../, ..\, %00, URL-encoded ..%2F)
 *  5. Polyglot file detection (JPEG + PHP/HTML, PDF + embedded JS)
 *  6. Simpan ke storage/temporary/ dengan nama random UUID
 */
class SecureFileUploadService
{
    private const MAX_SIZE_BYTES = 2 * 1024 * 1024; // 2 MB
    private const MAX_SIZE_KB = 2048;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'application/pdf',
    ];

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];

    private const STORAGE_DISK = 'temporary';
    private const STORAGE_DIR = 'temporary';

    /**
     * Validasi file secara lengkap.
     *
     * @param  UploadedFile  $file
     * @return ValidationResult
     */
    public function validateFile(UploadedFile $file): ValidationResult
    {
        $errors = [];

        // 1. Validasi ukuran
        $sizeCheck = $this->checkFileSize($file);
        if (! $sizeCheck['valid']) {
            $errors[] = $sizeCheck['error'];
        }

        // 2. Validasi MIME type server-side
        $mimeCheck = $this->checkMimeType($file);
        if (! $mimeCheck['valid']) {
            $errors[] = $mimeCheck['error'];
        }

        // 3. Validasi ekstensi
        $extCheck = $this->checkExtension($file);
        if (! $extCheck['valid']) {
            $errors[] = $extCheck['error'];
        }

        // 4. Scan path traversal
        $traversalCheck = $this->scanPathTraversal($file);
        if (! $traversalCheck['valid']) {
            $errors[] = $traversalCheck['error'];
        }

        // 5. Deteksi polyglot file
        $polyglotCheck = $this->detectPolyglot($file);
        if (! $polyglotCheck['valid']) {
            $errors[] = $polyglotCheck['error'];
        }

        $valid = empty($errors);

        Log::info('SecureFileUploadService::validateFile', [
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $file->getSize(),
            'server_mime' => $mimeCheck['mime'] ?? null,
            'valid' => $valid,
            'error_count' => count($errors),
        ]);

        return new ValidationResult($valid, $errors, [
            'size_bytes' => $file->getSize(),
            'mime' => $mimeCheck['mime'] ?? null,
            'extension' => strtolower($file->getClientOriginalExtension()),
        ]);
    }

    /**
     * Simpan file ke storage temporary dengan nama random UUID.
     *
     * @param  UploadedFile  $file
     * @param  string  $context  Konteks penyimpanan (misal: ktp, kk)
     * @return StoreResult
     */
    public function storeFile(UploadedFile $file, string $context = 'file'): StoreResult
    {
        $validation = $this->validateFile($file);
        if (! $validation->isValid()) {
            return new StoreResult(
                false,
                null,
                $validation->errors,
                'VALIDATION_FAILED',
                null
            );
        }

        try {
            $uuid = Str::uuid()->toString();
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = "{$uuid}.{$extension}";
            $dir = self::STORAGE_DIR . "/{$context}";

            $path = Storage::disk(self::STORAGE_DISK)->putFileAs($dir, $file, $filename);

            if ($path === false || $path === '') {
                Log::error('SecureFileUploadService: Storage putFileAs failed', [
                    'filename' => $filename,
                    'dir' => $dir,
                ]);

                return new StoreResult(
                    false,
                    null,
                    ['Gagal menyimpan file ke storage.'],
                    'STORAGE_ERROR',
                    null
                );
            }

            $diskPath = Storage::disk(self::STORAGE_DISK)->path($path);

            Log::info('SecureFileUploadService: File stored', [
                'uuid' => $uuid,
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size_bytes' => $file->getSize(),
                'disk_path' => $diskPath,
            ]);

            return new StoreResult(
                true,
                $path,
                [],
                'SUCCESS',
                $diskPath
            );
        } catch (\Throwable $e) {
            Log::error('SecureFileUploadService: Exception during storeFile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new StoreResult(
                false,
                null,
                ['Terjadi kesalahan saat menyimpan file.'],
                'EXCEPTION',
                null
            );
        }
    }

    /**
     * Periksa ukuran file tidak melebihi 2MB.
     *
     * @return array{valid:bool,error:?string}
     */
    private function checkFileSize(UploadedFile $file): array
    {
        $size = $file->getSize();

        if ($size === null || $size === 0) {
            return [
                'valid' => false,
                'error' => 'Ukuran file tidak valid atau file kosong.',
            ];
        }

        if ($size > self::MAX_SIZE_BYTES) {
            return [
                'valid' => false,
                'error' => "Ukuran file ({$size} bytes) melebihi batas maksimal " . self::MAX_SIZE_KB . " KB (2 MB).",
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Validasi MIME type menggunakan finfo_file (server-side).
     * Mencegah content-type spoofing.
     *
     * @return array{valid:bool,error:?string,mime:?string}
     */
    private function checkMimeType(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();

        if ($realPath === false || $realPath === '' || ! is_file($realPath)) {
            return [
                'valid' => false,
                'error' => 'File tidak dapat dibaca oleh server.',
                'mime' => null,
            ];
        }

        $mime = null;

        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_file($finfo, $realPath);
                finfo_close($finfo);
            }
        }

        if ($mime === null && function_exists('mime_content_type')) {
            $mime = mime_content_type($realPath);
        }

        if ($mime === null || $mime === false || $mime === '') {
            Log::warning('SecureFileUploadService: Cannot detect MIME type', [
                'real_path' => $realPath,
            ]);

            return [
                'valid' => false,
                'error' => 'Tidak dapat memverifikasi tipe file.',
                'mime' => null,
            ];
        }

        $mime = strtolower($mime);

        if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            return [
                'valid' => false,
                'error' => "Tipe file tidak diizinkan ({$mime}). Hanya PDF, JPG, PNG yang diterima.",
                'mime' => $mime,
            ];
        }

        return ['valid' => true, 'error' => null, 'mime' => $mime];
    }

    /**
     * Validasi ekstensi file case-insensitive.
     *
     * @return array{valid:bool,error:?string}
     */
    private function checkExtension(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName() ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === '') {
            return [
                'valid' => false,
                'error' => 'File tanpa ekstensi tidak diizinkan.',
            ];
        }

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return [
                'valid' => false,
                'error' => "Ekstensi file '.{$extension}' tidak diizinkan. Hanya .pdf, .jpg, .png yang diterima.",
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Scan konten file untuk pola path traversal.
     *
     * @return array{valid:bool,error:?string}
     */
    private function scanPathTraversal(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();

        if ($realPath === false || $realPath === '' || ! is_file($realPath)) {
            return ['valid' => true, 'error' => null];
        }

        $content = @file_get_contents($realPath, false, null, 0, 8192);

        if ($content === false || $content === '') {
            return ['valid' => true, 'error' => null];
        }

        // Pola path traversal yang umum
        $traversalPatterns = [
            '../',
            '..\\',
            '%00',
            '%2e%2e/',
            '%2e%2e\\',
            '..%2F',
            '..%5C',
        ];

        foreach ($traversalPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                Log::warning('SecureFileUploadService: Path traversal detected', [
                    'pattern' => bin2hex($pattern),
                    'original_name' => $file->getClientOriginalName(),
                ]);

                return [
                    'valid' => false,
                    'error' => 'File mencurigakan contain pola path traversal dan tidak dapat diterima.',
                ];
            }
        }

        // Scan filename too
        $filename = $file->getClientOriginalName() ?? '';
        foreach ($traversalPatterns as $pattern) {
            if (str_contains($filename, $pattern)) {
                Log::warning('SecureFileUploadService: Path traversal in filename', [
                    'pattern' => bin2hex($pattern),
                    'filename' => $filename,
                ]);

                return [
                    'valid' => false,
                    'error' => 'Nama file mencurigakan dan tidak dapat diterima.',
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Deteksi polyglot file (file yang merupakan kombinasi dua format).
     *
     * Polyglot yang dicegat:
     *  - JPEG dengan embedded PHP/HTML/JavaScript
     *  - PDF dengan embedded JavaScript
     *
     * @return array{valid:bool,error:?string}
     */
    private function detectPolyglot(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();

        if ($realPath === false || $realPath === '' || ! is_file($realPath)) {
            return ['valid' => true, 'error' => null];
        }

        $handle = fopen($realPath, 'rb');
        if ($handle === false) {
            return ['valid' => true, 'error' => null];
        }

        $header = fread($handle, 8192);
        fclose($handle);

        if ($header === false || $header === '') {
            return ['valid' => true, 'error' => null];
        }

        // JPEG polyglot detection
        if ($this->startsWithJpegHeader($header)) {
            $check = $this->detectJpegPolyglot($header);
            if (! $check['valid']) {
                return $check;
            }
        }

        // PDF polyglot detection
        if ($this->startsWithPdfHeader($header)) {
            $check = $this->detectPdfPolyglot($header);
            if (! $check['valid']) {
                return $check;
            }
        }

        return ['valid' => true, 'error' => null];
    }

    private function startsWithJpegHeader(string $header): bool
    {
        $jpegSignatures = [
            "\xFF\xD8\xFF\xE0",
            "\xFF\xD8\xFF\xE1",
            "\xFF\xD8\xFF\xE8",
            "\xFF\xD8\xFF\xDB",
            "\xFF\xD8\xFF\xEE",
        ];

        foreach ($jpegSignatures as $sig) {
            if (str_starts_with($header, $sig)) {
                return true;
            }
        }

        return str_starts_with($header, "\xFF\xD8");
    }

    private function startsWithPdfHeader(string $header): bool
    {
        return str_starts_with($header, '%PDF-');
    }

    /**
     * Deteksi PHP/HTML/JS embedded dalam file JPEG.
     *
     * @param  string  $header
     * @return array{valid:bool,error:?string}
     */
    private function detectJpegPolyglot(string $header): array
    {
        $maliciousPatterns = [
            '<?php',
            '<?=',
            '<script',
            '<html',
            '<svg',
            '<iframe',
            'javascript:',
            'onerror=',
            'onload=',
            'eval(',
            'document.write',
        ];

        $headerLower = strtolower($header);

        foreach ($maliciousPatterns as $pattern) {
            if (str_contains($headerLower, $pattern)) {
                Log::warning('SecureFileUploadService: JPEG polyglot detected', [
                    'pattern' => $pattern,
                ]);

                return [
                    'valid' => false,
                    'error' => 'File JPEG mencurigakan contain kode executable dan tidak dapat diterima.',
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Deteksi JavaScript atau kode executable embedded dalam file PDF.
     *
     * @param  string  $header
     * @return array{valid:bool,error:?string}
     */
    private function detectPdfPolyglot(string $header): array
    {
        $maliciousPatterns = [
            '/JS',
            '/JavaScript',
            '/AA',
            '/OpenAction',
            'eval(',
            'this.submitForm',
            'this.exportDataObject',
        ];

        $headerLower = strtolower($header);

        foreach ($maliciousPatterns as $pattern) {
            if (str_contains($headerLower, $pattern)) {
                Log::warning('SecureFileUploadService: PDF polyglot detected', [
                    'pattern' => $pattern,
                ]);

                return [
                    'valid' => false,
                    'error' => 'File PDF mencurigakan contain JavaScript atau aksi otomatis dan tidak dapat diterima.',
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Generate nama file UUID tanpa ekstensi.
     */
    public function generateUuidFilename(string $extension): string
    {
        return Str::uuid()->toString() . '.' . strtolower(trim($extension, '.'));
    }
}

/**
 * Immutable result object untuk validasi file.
 *
 * @property bool $valid
 * @property array<string> $errors
 * @property array<string,mixed> $meta
 */
class ValidationResult
{
    /**
     * @param  bool              $valid
     * @param  array<string>     $errors
     * @param  array<string,mixed> $meta
     */
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors,
        public readonly array $meta = []
    ) {}

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function errorCodes(): array
    {
        return array_map(fn (string $e): string => $this->mapErrorToCode($e), $this->errors);
    }

    private function mapErrorToCode(string $error): string
    {
        return match (true) {
            str_contains($error, 'Ukuran file') => 'SIZE_EXCEEDED',
            str_contains($error, 'Tipe file') || str_contains($error, 'tidak diizinkan') => 'INVALID_MIME',
            str_contains($error, 'Ekstensi') => 'INVALID_EXTENSION',
            str_contains($error, 'path traversal') || str_contains($error, 'mencurigakan') => 'MALICIOUS_CONTENT',
            str_contains($error, 'tidak dapat dibaca') => 'UNREADABLE',
            str_contains($error, 'Gagal menyimpan') => 'STORAGE_ERROR',
            default => 'VALIDATION_ERROR',
        };
    }
}

/**
 * Immutable result object untuk penyimpanan file.
 *
 * @property bool $success
 * @property string|null $path   Path relatif di storage disk
 * @property array<string> $errors
 * @property string $code        SUCCESS | VALIDATION_FAILED | STORAGE_ERROR | EXCEPTION
 * @property string|null $diskPath Path absolut di filesystem
 */
class StoreResult
{
    /**
     * @param  bool              $success
     * @param  string|null        $path
     * @param  array<string>     $errors
     * @param  string            $code
     * @param  string|null        $diskPath
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $path,
        public readonly array $errors,
        public readonly string $code,
        public readonly ?string $diskPath = null
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}