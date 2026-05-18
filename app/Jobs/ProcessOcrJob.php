<?php

namespace App\Jobs;

use App\Models\AntrianOnline;
use App\Services\EasyOcrService;
use App\Services\AdvancedKtpOcrParsingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * ProcessOcrJob - Queue job for async OCR processing
 *
 * Handles:
 * - File processing via EasyOCR
 * - Result parsing and storage
 * - Status updates
 * - Callback notifications
 *
 * Disdukcapil Project - Anggota 5
 */
class ProcessOcrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 180; // 3 minutes
    public $backoff = [10, 30, 60]; // Retry delays in seconds

    private ?string $filePath = null;
    private string $antrianId;
    private string $clientIp;
    private ?string $callbackUrl = null;
    private bool $reprocess = false;

    /**
     * Create a new job instance.
     */
    public function __construct(
        $file, // Can be UploadedFile or null for reprocess
        string $antrianId,
        string $clientIp = '127.0.0.1',
        ?string $callbackUrl = null,
        bool $reprocess = false
    ) {
        $this->antrianId = $antrianId;
        $this->clientIp = $clientIp;
        $this->callbackUrl = $callbackUrl;
        $this->reprocess = $reprocess;

        // Store file if provided
        if ($file && !$reprocess) {
            $this->filePath = $this->storeFile($file);
        }

        // Set queue name
        $this->onQueue('ocr');
    }

    /**
     * Execute the job.
     */
    public function handle(
        EasyOcrService $ocrService,
        AdvancedKtpOcrParsingService $parsingService
    ): void {
        $startTime = microtime(true);

        Log::info('ProcessOcrJob: Starting', [
            'antrian_id' => $this->antrianId,
            'reprocess' => $this->reprocess,
        ]);

        try {
            // Update status to processing
            $this->updateStatus('processing');

            // Get file path
            $imagePath = $this->getImagePath();

            if (!$imagePath || !file_exists($imagePath)) {
                throw new \Exception('Image file not found');
            }

            // Create temporary file for OCR service
            $tempFile = $this->createTempFile($imagePath);

            // Process with EasyOCR
            $ocrResult = $ocrService->processKtpImage(
                $this->createUploadedFile($tempFile),
                $this->antrianId
            );

            // Cleanup temp file
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if (!$ocrResult['success']) {
                throw new \Exception($ocrResult['message'] ?? 'OCR processing failed');
            }

            // Parse the result
            $parsed = $parsingService->parse($ocrResult['raw_text']);

            // Save to database
            $this->saveResult($ocrResult, $parsed);

            $processingTime = microtime(true) - $startTime;

            Log::info('ProcessOcrJob: Completed', [
                'antrian_id' => $this->antrianId,
                'confidence' => $parsed['confidence'] ?? 0,
                'processing_time' => round($processingTime, 2),
            ]);

            // Update status to completed
            $this->updateStatus('completed', [
                'confidence' => $parsed['confidence'] ?? 0,
                'processing_time' => $processingTime,
            ]);

            // Send callback if provided
            if ($this->callbackUrl) {
                $this->sendCallback($parsed, $processingTime);
            }

        } catch (\Throwable $e) {
            Log::error('ProcessOcrJob: Failed', [
                'antrian_id' => $this->antrianId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->updateStatus('failed', [
                'error' => $e->getMessage(),
            ]);

            // Release job back to queue for retry
            $this->release(30);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessOcrJob: Failed permanently', [
            'antrian_id' => $this->antrianId,
            'error' => $exception->getMessage(),
        ]);

        $this->updateStatus('failed', [
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Store uploaded file
     */
    private function storeFile($file): string
    {
        $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('ocr/uploads', $filename);

        return $path;
    }

    /**
     * Get image path for processing
     */
    private function getImagePath(): ?string
    {
        if ($this->reprocess) {
            // Get original file from antrian
            $antrian = AntrianOnline::find($this->antrianId);
            return $antrian?->file_ktp_path ? storage_path('app/' . $antrian->file_ktp_path) : null;
        }

        return $this->filePath ? storage_path('app/' . $this->filePath) : null;
    }

    /**
     * Create temporary file for OCR
     */
    private function createTempFile(string $originalPath): string
    {
        $tempDir = storage_path('app/temp/ocr');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . Str::uuid() . '.tmp';
        copy($originalPath, $tempPath);

        return $tempPath;
    }

    /**
     * Create UploadedFile from temp file
     */
    private function createUploadedFile(string $path): \Illuminate\Http\UploadedFile
    {
        return new \Illuminate\Http\UploadedFile(
            $path,
            basename($path),
            mime_content_type($path),
            null,
            true
        );
    }

    /**
     * Save OCR result to database
     */
    private function saveResult(array $ocrResult, array $parsed): void
    {
        $antrian = AntrianOnline::find($this->antrianId);

        if (!$antrian) {
            // Create new record if not exists
            $antrian = AntrianOnline::create([
                'id' => $this->antrianId,
                'status' => 'Dokumen Diterima',
            ]);
        }

        // Update with OCR results
        $antrian->update([
            'ocr_raw_text' => $ocrResult['raw_text'] ?? null,
            'ocr_confidence' => $parsed['confidence'] ?? $ocrResult['confidence'] ?? 0,
            'ocr_field_confidence' => $parsed['field_confidence'] ?? $ocrResult['field_confidence'] ?? [],
            'nik' => $parsed['data']['nik'] ?? null,
            'nama_lengkap' => $parsed['data']['nama_lengkap'] ?? null,
            'tempat_lahir' => $parsed['data']['tempat_lahir'] ?? null,
            'tanggal_lahir' => $parsed['data']['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $parsed['data']['jenis_kelamin'] ?? null,
            'alamat' => $parsed['data']['alamat'] ?? null,
        ]);

        // Save to cache for quick access
        cache()->put("ocr_result:{$this->antrianId}", $parsed, 3600);
    }

    /**
     * Update processing status
     */
    private function updateStatus(string $status, array $data = []): void
    {
        $statusData = array_merge([
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ], $data);

        cache()->put("ocr_status:{$this->antrianId}", $statusData, 3600);

        // Also update in database if antrian exists
        $antrian = AntrianOnline::find($this->antrianId);
        if ($antrian) {
            if ($status === 'completed') {
                $antrian->update(['status' => 'Verifikasi Data']);
            } elseif ($status === 'failed') {
                $antrian->update(['status' => 'Menunggu']); // Reset to waiting
            }
        }
    }

    /**
     * Send callback notification
     */
    private function sendCallback(array $parsed, float $processingTime): void
    {
        try {
            Http::timeout(10)->post($this->callbackUrl, [
                'antrian_id' => $this->antrianId,
                'status' => 'completed',
                'confidence' => $parsed['confidence'] ?? 0,
                'data' => $parsed['data'] ?? [],
                'processing_time' => round($processingTime, 2),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ProcessOcrJob: Callback failed', [
                'url' => $this->callbackUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get unique job ID
     */
    public function uniqueId(): string
    {
        return "ocr:{$this->antrianId}";
    }
}
