<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\KtpOcrException;
use App\Http\Controllers\Controller;
use App\Http\Requests\KtpUploadRequest;
use App\Models\AntrianOnline;
use App\Services\KtpOcrExtractionService;
use App\Services\KtpOcrService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class KtpOcrController extends Controller
{
    public function __construct(
        private readonly KtpOcrService $service,
        private readonly KtpOcrExtractionService $extractionService
    ) {
    }

    /**
     * POST /api/ktp/upload
     * Upload gambar KTP dan proses OCR langsung dari Laravel.
     */
    public function upload(KtpUploadRequest $request): JsonResponse
    {
        $antrianId = (string) $request->validated('antrian_online_id');
        $file = $request->file('ktp_image');

        /** @var AntrianOnline|null $antrian */
        $antrian = AntrianOnline::query()->where('antrian_online_id', $antrianId)->first();
        if ($antrian === null) {
            return $this->respond(false, 'Antrian tidak ditemukan.', null, 404);
        }

        if ($antrian->status_antrian !== AntrianOnline::STATUS_MENUNGGU) {
            return $this->respond(
                false,
                'Antrian tidak dalam status Menunggu, upload KTP tidak diizinkan.',
                ['status_antrian' => $antrian->status_antrian],
                409
            );
        }

        $result = $this->service->processKtpImage($antrianId, $file);
        if (! ($result['success'] ?? false)) {
            $antrian->status_antrian = AntrianOnline::STATUS_MENUNGGU;
            $antrian->save();
            return $this->respond(false, 'Gagal mengirim gambar KTP ke layanan OCR.', [
                'error_code' => 'VISION_OCR_FAILED',
                'detail' => $result['message'] ?? 'Unknown error',
            ], 400);
        }

        $antrian->refresh();

        return $this->respond(
            true,
            'KTP berhasil diproses.',
            [
                'antrian_id' => $antrian->antrian_online_id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'status' => $antrian->status_antrian,
                'nik' => $result['data']['nik'] ?? null,
                'nama_lengkap' => $result['data']['nama_lengkap'] ?? null,
                'alamat' => $result['data']['alamat'] ?? null,
                'confidence' => $result['data']['confidence'] ?? 0.5,
            ],
            202
        );
    }

    /**
     * POST /api/ktp/webhook
     * Callback dari GCP Cloud Function Python (extract-ktp).
     */
    public function webhook(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-GCP-Signature');

        if (! $this->service->verifyWebhookSignature($rawBody, is_array($signature) ? ($signature[0] ?? null) : $signature)) {
            Log::warning('KtpOcrController::webhook — signature invalid', [
                'ip' => $request->ip(),
                'has_signature' => $signature !== null,
            ]);

            return $this->respond(false, 'Signature webhook tidak valid.', null, 400);
        }

        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return $this->respond(false, 'Payload webhook bukan JSON valid.', null, 422);
        }

        try {
            $antrian = $this->service->handleWebhookPayload($payload);
        } catch (ModelNotFoundException) {
            return $this->respond(false, 'Antrian tidak ditemukan untuk antrian_online_id yang diberikan.', null, 404);
        } catch (KtpOcrException $e) {
            return $this->respond(false, $e->getMessage(), null, $e->getCode() ?: 422);
        }

        return $this->respond(true, 'Data tersimpan.', [
            'antrian_online_id' => $antrian->antrian_online_id,
            'status_antrian' => $antrian->status_antrian,
        ], 200);
    }

    /**
     * GET /api/ktp/status/{antrian_online_id}
     */
    public function status(string $antrianId): JsonResponse
    {
        try {
            $data = $this->service->getStatus($antrianId);
        } catch (ModelNotFoundException) {
            return $this->respond(false, 'Antrian tidak ditemukan.', null, 404);
        }

        return $this->respond(true, 'Status antrian ditemukan.', $data, 200);
    }

    /**
     * POST /api/ocr/ktp/extract
     * Upload file KTP dan ekstrak data menggunakan OCR pipeline.
     */
    public function extract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'provider' => [
                'nullable',
                'string',
                'in:easyocr,google_vision,ocrspace',
            ],
        ], [
            'file.required' => 'File KTP wajib diunggah.',
            'file.file' => 'Field file harus berupa file valid.',
            'file.mimes' => 'Format file harus JPG, JPEG, PNG, atau PDF.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $file = $request->file('file');
        $provider = $validated['provider'] ?? $this->resolveDefaultProvider();

        Log::info('KtpOcrController::extract — incoming request', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'provider' => $provider,
            'ip' => $request->ip(),
        ]);

        try {
            $preprocessedPath = $this->preprocessImage($file);
            $rawText = $this->runOcr($preprocessedPath, $provider, $file->getClientOriginalExtension());
            $result = $this->extractionService->extract($rawText, $preprocessedPath);

            $this->cleanupTempFile($preprocessedPath);

            if ($result['needs_manual_review']) {
                Log::warning('KtpOcrController::extract — low confidence, flagged for manual review', [
                    'confidence' => $result['confidence'],
                    'nik_masked' => $result['nik'] !== ''
                        ? (substr($result['nik'], 0, 6) . '****' . substr($result['nik'], -4))
                        : null,
                ]);
            }

            Log::info('KtpOcrController::extract — extraction complete', [
                'nik_masked' => $result['nik'] !== ''
                    ? (substr($result['nik'], 0, 6) . '****' . substr($result['nik'], -4))
                    : null,
                'confidence' => $result['confidence'],
                'needs_review' => $result['needs_manual_review'],
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'nik' => $result['nik'],
                    'nama_lengkap' => $result['nama_lengkap'],
                    'tempat_lahir' => $result['tempat_lahir'],
                    'tanggal_lahir' => $result['tanggal_lahir'],
                    'alamat' => $result['alamat'],
                    'rt_rw' => $result['rt_rw'],
                    'kel_desa' => $result['kel_desa'],
                    'kecamatan' => $result['kecamatan'],
                    'agama' => $result['agama'],
                    'status_perkawinan' => $result['status_perkawinan'],
                ],
                'confidence' => $result['confidence'],
                'field_confidence' => $result['field_confidence'],
                'needs_manual_review' => $result['needs_manual_review'],
                'provider' => $provider,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('KtpOcrController::extract — extraction failed', [
                'error' => $e->getMessage(),
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'OCR_EXTRACTION_FAILED',
                    'message' => 'Gagal mengekstrak data dari gambar KTP.',
                    'detail' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    private function preprocessImage(UploadedFile $file): string
    {
        $tempPath = storage_path('app/temporary/ocr_' . bin2hex(random_bytes(8)) . '.jpg');
        $tempDir = dirname($tempPath);

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            $this->convertPdfToImage($file, $tempPath);
            return $tempPath;
        }

        $image = Image::make($file->getRealPath());

        if ($image->width() > 2048 || $image->height() > 2048) {
            $image->resize(2048, 2048, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->greyscale();
        $image->contrast(15);
        $image->sharpen(1);

        $image->save($tempPath, 85, 'jpeg');

        return $tempPath;
    }

    private function convertPdfToImage(UploadedFile $file, string $outputPath): void
    {
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($file->getRealPath() . '[0]');
        $imagick->setImageFormat('jpeg');
        $imagick->setImageColorspace(\Imagick::COLORSPACE_GRAY);
        $imagick->writeImage($outputPath);
        $imagick->clear();
        $imagick->destroy();
    }

    private function runOcr(string $filePath, string $provider, string $originalExtension): string
    {
        return match ($provider) {
            'easyocr' => $this->runEasyOcr($filePath),
            'google_vision' => $this->runGoogleVision($filePath),
            'ocrspace' => $this->runOcrSpace($filePath),
            default => $this->runEasyOcrOrFallback($filePath),
        };
    }

    private function runEasyOcr(string $filePath): string
    {
        $apiUrl = config('services.easyocr.api_url');

        if ($apiUrl && (bool) config('services.easyocr.use_api')) {
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->attach('image', file_get_contents($filePath), 'ktp.jpg')
                ->post($apiUrl);

            if ($response->successful()) {
                $body = $response->json();
                return $this->normalizeOcrText($body['text'] ?? $body['raw_text'] ?? '', $body);
            }
        }

        throw new \RuntimeException('EasyOCR API tidak tersedia. Gunakan provider lain atau aktifkan EasyOCR API di konfigurasi.');
    }

    private function runGoogleVision(string $filePath): string
    {
        $apiKey = config('services.google_vision.api_key');

        if ($apiKey) {
            $base64 = base64_encode(file_get_contents($filePath));

            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->post('https://vision.googleapis.com/v1/images:annotate?key=' . $apiKey, [
                    'requests' => [
                        [
                            'image' => ['content' => $base64],
                            'features' => [
                                ['type' => 'DOCUMENT_TEXT_DETECTION', 'maxResults' => 1],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $text = $body['responses'][0]['textAnnotations'][0]['description'] ?? '';
                return $this->normalizeOcrText($text, $body);
            }
        }

        throw new \RuntimeException('Google Vision API tidak tersedia. Pastikan GOOGLE_VISION_API_KEY dikonfigurasi.');
    }

    private function runOcrSpace(string $filePath): string
    {
        $apiKey = config('services.ocr_space.api_key');

        if ($apiKey) {
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->attach('file', file_get_contents($filePath), 'ktp.jpg')
                ->post(config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image'), [
                    'apikey' => $apiKey,
                    'language' => 'ind',
                    'isOverlayRequired' => 'false',
                    'filetype' => 'Auto',
                ]);

            if ($response->successful()) {
                $body = $response->json();

                if (!empty($body['ParsedResults'][0]['ParsedText'])) {
                    return $this->normalizeOcrText($body['ParsedResults'][0]['ParsedText'], $body);
                }

                if (!empty($body['ErrorMessage'])) {
                    throw new \RuntimeException('OCR.space error: ' . $body['ErrorMessage']);
                }
            }
        }

        throw new \RuntimeException('OCR.space tidak tersedia. Pastikan OCR_SPACE_API_KEY dikonfigurasi.');
    }

    private function runEasyOcrOrFallback(string $filePath): string
    {
        $tried = [];

        try {
            return $this->runEasyOcr($filePath);
        } catch (\Throwable) {
            $tried[] = 'easyocr';
        }

        try {
            return $this->runGoogleVision($filePath);
        } catch (\Throwable) {
            $tried[] = 'google_vision';
        }

        try {
            return $this->runOcrSpace($filePath);
        } catch (\Throwable) {
            $tried[] = 'ocrspace';
        }

        throw new \RuntimeException(
            'Semua provider OCR gagal. Dicoba: ' . implode(', ', $tried)
        );
    }

    private function normalizeOcrText(string $text, array $rawResponse): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[^\S\n]+/', ' ', $text) ?? $text;

        return trim($text);
    }

    private function cleanupTempFile(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    private function resolveDefaultProvider(): string
    {
        if ((bool) config('services.easyocr.use_api') && config('services.easyocr.api_url')) {
            return 'easyocr';
        }
        if (config('services.google_vision.api_key')) {
            return 'google_vision';
        }
        if ((bool) config('services.ocr_space.enabled') && config('services.ocr_space.api_key')) {
            return 'ocrspace';
        }

        return 'easyocr';
    }
     * @param  array<string, mixed>|null  $data
     */
    private function respond(bool $success, string $message, ?array $data, int $status): JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
