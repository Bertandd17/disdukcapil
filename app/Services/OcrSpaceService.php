<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OcrSpaceService
{
    public function __construct(
        private readonly EasyOcrParsingService $parser
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.ocr_space.enabled', false)
            && filled(config('services.ocr_space.api_key'));
    }

    public function processKtpImage(UploadedFile|string $file): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'OCR.space belum dikonfigurasi.',
            ];
        }

        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $filename = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);

        if (!$path || !is_file($path)) {
            return [
                'success' => false,
                'error' => 'File gambar tidak ditemukan.',
            ];
        }

        $preparedFile = $this->prepareUploadFile($path, $filename);

        try {
            $response = Http::timeout((int) config('services.ocr_space.timeout', 60))
                ->attach('file', file_get_contents($preparedFile['path']), $preparedFile['filename'])
                ->post((string) config('services.ocr_space.endpoint'), [
                    'apikey' => (string) config('services.ocr_space.api_key'),
                    'language' => (string) config('services.ocr_space.language', 'auto'),
                    'OCREngine' => (string) config('services.ocr_space.engine', '2'),
                    'scale' => 'true',
                    'isTable' => 'false',
                    'isOverlayRequired' => 'false',
                    'detectOrientation' => 'true',
                ]);

            if (!$response->successful()) {
                Log::warning('OCR.space request failed', [
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 500),
                ]);

                return [
                    'success' => false,
                    'error' => 'OCR.space request gagal dengan status ' . $response->status(),
                ];
            }

            $payload = $response->json();
            if (!is_array($payload)) {
                return [
                    'success' => false,
                    'error' => 'Response OCR.space tidak valid.',
                ];
            }

            if (($payload['IsErroredOnProcessing'] ?? false) === true) {
                $message = $payload['ErrorMessage'] ?? $payload['ErrorDetails'] ?? 'OCR.space gagal memproses gambar.';

                return [
                    'success' => false,
                    'error' => is_array($message) ? implode(' ', $message) : (string) $message,
                ];
            }

            $rawText = $this->extractParsedText($payload);
            if ($rawText === '') {
                return [
                    'success' => false,
                    'error' => 'OCR.space tidak menemukan teks pada gambar.',
                ];
            }

            $parsed = $this->parser->parse($rawText);
            $confidence = (float) ($parsed['confidence'] ?? 0);

            return [
                'success' => true,
                'raw_text' => $rawText,
                'data' => $this->normalizeData($parsed),
                'confidence' => $confidence,
                'field_confidence' => $parsed['field_confidence'] ?? [],
                'ocr_source' => 'ocr_space',
                'ocr_meta' => [
                    'engine' => config('services.ocr_space.engine', '2'),
                    'language' => config('services.ocr_space.language', 'auto'),
                    'ocr_exit_code' => $payload['OCRExitCode'] ?? null,
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('OCR.space exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'OCR.space error: ' . $e->getMessage(),
            ];
        } finally {
            if (($preparedFile['cleanup'] ?? false) && is_file($preparedFile['path'])) {
                @unlink($preparedFile['path']);
            }
        }
    }

    private function prepareUploadFile(string $path, string $filename): array
    {
        $maxBytes = (int) config('services.ocr_space.max_file_size', 1450000);

        if (filesize($path) <= $maxBytes) {
            return [
                'path' => $path,
                'filename' => $filename,
                'cleanup' => false,
            ];
        }

        if (!function_exists('imagecreatefromstring')) {
            return [
                'path' => $path,
                'filename' => $filename,
                'cleanup' => false,
            ];
        }

        $contents = file_get_contents($path);
        $source = $contents === false ? false : @imagecreatefromstring($contents);
        if (!$source) {
            return [
                'path' => $path,
                'filename' => $filename,
                'cleanup' => false,
            ];
        }

        $tempDir = storage_path('app/temp/ocrspace');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);
        $maxDimension = (int) config('services.ocr_space.max_dimension', 1600);
        $scale = min(1, $maxDimension / max($originalWidth, $originalHeight));
        $targetWidth = max(1, (int) floor($originalWidth * $scale));
        $targetHeight = max(1, (int) floor($originalHeight * $scale));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
        imagedestroy($source);

        $outputPath = $tempDir . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . '_' . uniqid('', true) . '.jpg';
        $qualities = [82, 74, 66, 58, 50, 42];
        $currentImage = $resized;
        $currentWidth = $targetWidth;
        $currentHeight = $targetHeight;

        for ($attempt = 0; $attempt < 4; $attempt++) {
            foreach ($qualities as $quality) {
                imagejpeg($currentImage, $outputPath, $quality);
                if (is_file($outputPath) && filesize($outputPath) <= $maxBytes) {
                    break 2;
                }
            }

            $nextWidth = max(1, (int) floor($currentWidth * 0.85));
            $nextHeight = max(1, (int) floor($currentHeight * 0.85));
            $nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
            $white = imagecolorallocate($nextImage, 255, 255, 255);
            imagefilledrectangle($nextImage, 0, 0, $nextWidth, $nextHeight, $white);
            imagecopyresampled($nextImage, $currentImage, 0, 0, 0, 0, $nextWidth, $nextHeight, $currentWidth, $currentHeight);

            if ($currentImage !== $resized) {
                imagedestroy($currentImage);
            }

            $currentImage = $nextImage;
            $currentWidth = $nextWidth;
            $currentHeight = $nextHeight;
        }

        if ($currentImage !== $resized) {
            imagedestroy($currentImage);
        }
        imagedestroy($resized);

        if (!is_file($outputPath)) {
            return [
                'path' => $path,
                'filename' => $filename,
                'cleanup' => false,
            ];
        }

        Log::info('OCR.space upload image prepared', [
            'original_size' => filesize($path),
            'prepared_size' => filesize($outputPath),
            'original_dimensions' => "{$originalWidth}x{$originalHeight}",
            'prepared_dimensions' => "{$currentWidth}x{$currentHeight}",
        ]);

        return [
            'path' => $outputPath,
            'filename' => pathinfo($filename, PATHINFO_FILENAME) . '.jpg',
            'cleanup' => true,
        ];
    }

    private function extractParsedText(array $payload): string
    {
        $texts = [];

        foreach (($payload['ParsedResults'] ?? []) as $result) {
            $text = trim((string) ($result['ParsedText'] ?? ''));
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return trim(implode("\n", $texts));
    }

    private function normalizeData(array $data): array
    {
        if (isset($data['kecamatan']) && !isset($data['kec'])) {
            $data['kec'] = $data['kecamatan'];
        }

        $fields = [
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
        foreach ($fields as $field) {
            $normalized[$field] = $data[$field] ?? '';
        }

        $normalized['field_confidence'] = $data['field_confidence'] ?? [];

        return $normalized;
    }
}
