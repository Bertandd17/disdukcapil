<?php

namespace App\Services;

use App\Exceptions\KtpOcrException;
use App\Models\AntrianOnline;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * KtpOcrService menjembatani Laravel dengan tiga Cloud Function GCP:
 *  - upload multipart ke HTTP trigger (http-ktp).
 *  - terima webhook hasil OCR dari Pub/Sub trigger Python (extract-ktp).
 *  - expose status pemrosesan untuk polling klien.
 *
 * Semua data hasil OCR HANYA disimpan ke kolom existing pada tabel
 * antrian_online: nik (encrypted), nama_lengkap, alamat.
 */
class KtpOcrService
{
    public const OCR_META_CACHE_PREFIX = 'ktp_ocr_meta:';

    public static function forgetOcrMetaCache(string $antrianId): void
    {
        Cache::forget(self::OCR_META_CACHE_PREFIX.$antrianId);
    }

    /**
     * Upload + proses OCR KTP dengan format response siap pakai API.
     *
     * @return array{success:bool,data:?array<string,mixed>,message:string}
     */
    public function processKtpImage(string $antrianOnlineId, UploadedFile $file): array
    {
        try {
            $this->validateFile($file);

            $ocrResult = $this->uploadToGcp($antrianOnlineId, $file);

            /** @var AntrianOnline|null $antrian */
            $antrian = AntrianOnline::query()
                ->where('antrian_online_id', $antrianOnlineId)
                ->first();

            if ($antrian === null) {
                throw new KtpOcrException('Antrian tidak ditemukan', 404, [
                    'antrian_online_id' => $antrianOnlineId,
                ]);
            }

            if (($ocrResult['status'] ?? '') !== 'processed' && $antrian->status_antrian === AntrianOnline::STATUS_MENUNGGU) {
                $antrian->status_antrian = AntrianOnline::STATUS_VERIFIKASI;
                $antrian->save();
            } else {
                $antrian->refresh();
            }

            $ocrMeta = Cache::get(self::OCR_META_CACHE_PREFIX.$antrianOnlineId);
            $confidence = is_array($ocrMeta) && isset($ocrMeta['confidence'])
                ? (float) $ocrMeta['confidence']
                : 0.5;

            return [
                'success' => true,
                'data' => [
                    'antrian_id' => $antrianOnlineId,
                    'status' => $antrian->status_antrian,
                    'nik' => $antrian->nik,
                    'nama_lengkap' => $antrian->nama_lengkap,
                    'alamat' => $antrian->alamat,
                    'confidence' => $confidence,
                    'file_id' => $ocrResult['file_id'] ?? null,
                    'gcs_path' => $ocrResult['gcs_path'] ?? null,
                ],
                'message' => 'KTP berhasil diproses',
            ];
        } catch (\Throwable $e) {
            Log::error('KTP OCR Error', [
                'antrian_online_id' => $antrianOnlineId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Gagal memproses KTP: '.$e->getMessage(),
            ];
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        $maxSize = 5 * 1024 * 1024;
        if (($file->getSize() ?? 0) > $maxSize) {
            throw new KtpOcrException('Ukuran file maksimal 5MB', 422);
        }

        $mime = strtolower((string) $file->getMimeType());
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (! in_array($mime, $allowedMimes, true)) {
            throw new KtpOcrException('Format file harus JPG, JPEG, atau PNG', 422);
        }
    }

    /**
     * Proses OCR langsung dari Laravel ke Google Vision API.
     *
     * @return array{status:string,file_id:?string,antrian_id:string,gcs_path:?string,raw:array<string,mixed>}
     *
     * @throws KtpOcrException
     */
    public function uploadToGcp(string $antrianId, UploadedFile $file): array
    {
        if ((bool) config('services.gcp_ktp.mock_enabled')) {
            return $this->mockUpload($antrianId, $file);
        }

        $localPath = $file->getRealPath() ?: $file->getPathname();
        if ($localPath === '' || ! is_readable($localPath)) {
            throw new KtpOcrException('File KTP tidak dapat dibaca dari server (path sementara). Coba unggah ulang atau periksa izin disk.', 500, [
                'antrian_online_id' => $antrianId,
            ]);
        }

        $credentialsPath = (string) config('services.gcp_ktp.vision_credentials_path', '');
        $clientOptions = [];
        if ($credentialsPath !== '') {
            if (! is_readable($credentialsPath)) {
                throw new KtpOcrException('File kredensial Google Vision tidak ditemukan atau tidak bisa dibaca.', 500, [
                    'antrian_online_id' => $antrianId,
                    'credentials_path' => $credentialsPath,
                ]);
            }
            $clientOptions['credentials'] = $credentialsPath;
        }

        $imageData = @file_get_contents($localPath);
        if ($imageData === false || $imageData === '') {
            throw new KtpOcrException('Gagal membaca bytes gambar KTP untuk OCR.', 500, [
                'antrian_online_id' => $antrianId,
            ]);
        }

        $client = null;
        try {
            $client = new ImageAnnotatorClient($clientOptions);
            $response = $client->documentTextDetection($imageData);
        } catch (\Throwable $e) {
            Log::error('KtpOcrService::uploadToGcp direct Vision exception', [
                'antrian_online_id' => $antrianId,
                'error' => $e->getMessage(),
            ]);

            throw new KtpOcrException('Tidak dapat memproses OCR dengan Google Vision API.', 502, [
                'antrian_online_id' => $antrianId,
            ], $e);
        } finally {
            if ($client !== null) {
                $client->close();
            }
        }

        $error = $response->getError();
        if ($error !== null && $error->getMessage() !== '') {
            Log::warning('KtpOcrService::uploadToGcp Vision returned error', [
                'antrian_online_id' => $antrianId,
                'error_code' => $error->getCode(),
                'error_message' => $error->getMessage(),
            ]);

            throw new KtpOcrException('Google Vision API mengembalikan error.', 502, [
                'antrian_online_id' => $antrianId,
                'vision_error_code' => $error->getCode(),
                'vision_error_message' => $error->getMessage(),
            ]);
        }

        $fullText = $response->getFullTextAnnotation();
        $rawText = $fullText !== null ? (string) $fullText->getText() : '';
        $parsed = $this->parseVisionText($rawText);
        $hasAnyParsedField = ($parsed['nik'] ?? '') !== ''
            || ($parsed['nama_lengkap'] ?? '') !== ''
            || ($parsed['alamat'] ?? '') !== '';

        if ($hasAnyParsedField) {
            $this->handleWebhookPayload([
                'antrian_online_id' => $antrianId,
                'nik' => $parsed['nik'] ?? '',
                'nama_lengkap' => $parsed['nama_lengkap'] ?? '',
                'alamat' => $parsed['alamat'] ?? '',
                'confidence' => 0.95,
                'field_confidence' => [
                    'nik' => ($parsed['nik'] ?? '') !== '' ? 0.95 : 0.0,
                    'nama_lengkap' => ($parsed['nama_lengkap'] ?? '') !== '' ? 0.90 : 0.0,
                    'alamat' => ($parsed['alamat'] ?? '') !== '' ? 0.90 : 0.0,
                ],
            ]);
        } else {
            Cache::put(
                self::OCR_META_CACHE_PREFIX.$antrianId,
                [
                    'confidence' => 0.0,
                    'field_confidence' => [
                        'nik' => 0.0,
                        'nama_lengkap' => 0.0,
                        'alamat' => 0.0,
                    ],
                ],
                now()->addMinutes(30)
            );
        }

        return [
            'status' => $hasAnyParsedField ? 'processed' : 'uploaded',
            'file_id' => null,
            'antrian_id' => $antrianId,
            'gcs_path' => null,
            'raw' => [
                'provider' => 'google_vision_direct',
                'text_length' => mb_strlen($rawText),
                'parsed' => $parsed,
                'parsed_any_field' => $hasAnyParsedField,
            ],
        ];
    }

    /**
     * @return array{nik:string,nama_lengkap:string,alamat:string}
     */
    private function parseVisionText(string $rawText): array
    {
        if (trim($rawText) === '') {
            return ['nik' => '', 'nama_lengkap' => '', 'alamat' => ''];
        }

        $cleanLines = array_values(array_filter(array_map(
            static fn (string $line): string => trim(preg_replace('/\s+/', ' ', $line) ?? ''),
            preg_split('/\R/u', $rawText) ?: []
        )));

        return [
            'nik' => $this->extractNik($rawText),
            'nama_lengkap' => $this->extractNama($cleanLines),
            'alamat' => $this->extractAlamat($cleanLines),
        ];
    }

    private function extractNik(string $rawText): string
    {
        $digitsOnly = preg_replace('/\D+/', '', $rawText) ?? '';
        if ($digitsOnly === '') {
            return '';
        }

        if (preg_match('/\d{16}/', $digitsOnly, $match) === 1) {
            return $match[0];
        }

        return '';
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function extractNama(array $lines): string
    {
        foreach ($lines as $index => $line) {
            $upper = mb_strtoupper($line);
            if (str_contains($upper, 'NAMA')) {
                $value = trim((string) preg_replace('/^.*NAMA\s*:?\s*/iu', '', $line));
                if ($value !== '' && mb_strtoupper($value) !== 'NAMA') {
                    return mb_substr($value, 0, 100);
                }

                $nextLine = $lines[$index + 1] ?? '';
                if ($nextLine !== '') {
                    return mb_substr($nextLine, 0, 100);
                }
            }
        }

        return '';
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function extractAlamat(array $lines): string
    {
        $stopKeywords = ['AGAMA', 'STATUS', 'PEKERJAAN', 'KEWARGANEGARAAN', 'BERLAKU', 'TEMPAT/TGL'];

        foreach ($lines as $index => $line) {
            $upper = mb_strtoupper($line);
            if (! str_contains($upper, 'ALAMAT')) {
                continue;
            }

            $parts = [];
            $value = trim((string) preg_replace('/^.*ALAMAT\s*:?\s*/iu', '', $line));
            if ($value !== '' && mb_strtoupper($value) !== 'ALAMAT') {
                $parts[] = $value;
            }

            for ($offset = 1; $offset <= 3; $offset++) {
                $nextLine = $lines[$index + $offset] ?? '';
                if ($nextLine === '') {
                    break;
                }
                $nextUpper = mb_strtoupper($nextLine);
                $isStop = false;
                foreach ($stopKeywords as $keyword) {
                    if (str_contains($nextUpper, $keyword)) {
                        $isStop = true;
                        break;
                    }
                }
                if ($isStop) {
                    break;
                }
                $parts[] = $nextLine;
            }

            if ($parts !== []) {
                return mb_substr(trim(implode(', ', $parts)), 0, 500);
            }
        }

        return '';
    }

    /**
     * Verifikasi HMAC signature webhook dari GCP.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) config('services.gcp_ktp.webhook_secret');
        if ($secret === '' || $signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, trim($signature));
    }

    /**
     * Terapkan payload webhook ke baris antrian_online.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws ModelNotFoundException jika antrian tidak ditemukan.
     * @throws KtpOcrException        jika payload tidak valid.
     */
    public function handleWebhookPayload(array $payload): AntrianOnline
    {
        $antrianId = isset($payload['antrian_online_id']) ? trim((string) $payload['antrian_online_id']) : '';
        if ($antrianId === '') {
            throw new KtpOcrException('Payload webhook tidak memiliki antrian_online_id.', 422);
        }

        $nik = isset($payload['nik']) ? trim((string) $payload['nik']) : '';
        $nama = isset($payload['nama_lengkap']) ? trim((string) $payload['nama_lengkap']) : '';
        $alamat = isset($payload['alamat']) ? trim((string) $payload['alamat']) : '';

        return DB::transaction(function () use ($antrianId, $nik, $nama, $alamat, $payload): AntrianOnline {
            /** @var AntrianOnline $antrian */
            $antrian = AntrianOnline::query()
                ->lockForUpdate()
                ->where('antrian_online_id', $antrianId)
                ->firstOrFail();

            $appliedNik = false;
            $appliedNama = false;
            $appliedAlamat = false;

            if ($nik !== '' && preg_match('/^\d{16}$/', $nik) === 1) {
                $antrian->nik = $nik;
                $appliedNik = true;
            }
            if ($nama !== '') {
                $antrian->nama_lengkap = mb_substr($nama, 0, 100);
                $appliedNama = true;
            }
            if ($alamat !== '') {
                $antrian->alamat = $alamat;
                $appliedAlamat = true;
            }

            $anyApplied = $appliedNik || $appliedNama || $appliedAlamat;

            if ($anyApplied) {
                $antrian->status_antrian = AntrianOnline::STATUS_DOKUMEN_DITERIMA;
            }

            $antrian->save();

            Log::info('KtpOcrService: webhook applied', [
                'antrian_online_id' => $antrian->antrian_online_id,
                'applied_nik' => $appliedNik,
                'applied_nama' => $appliedNama,
                'applied_alamat' => $appliedAlamat,
                'status_antrian' => $antrian->status_antrian,
                'confidence' => $payload['confidence'] ?? null,
            ]);

            if ($anyApplied) {
                $fieldConfidence = $payload['field_confidence'] ?? null;
                if (! is_array($fieldConfidence)) {
                    $fieldConfidence = null;
                }
                Cache::put(
                    self::OCR_META_CACHE_PREFIX.$antrian->antrian_online_id,
                    [
                        'confidence' => isset($payload['confidence']) ? (float) $payload['confidence'] : null,
                        'field_confidence' => $fieldConfidence,
                    ],
                    now()->addMinutes(30)
                );
            }

            return $antrian;
        });
    }

    /**
     * Status terkini untuk antrian (NIK dimasking).
     *
     * @return array<string, mixed>
     */
    public function getStatus(string $antrianId): array
    {
        /** @var AntrianOnline $antrian */
        $antrian = AntrianOnline::query()
            ->where('antrian_online_id', $antrianId)
            ->firstOrFail();

        return [
            'antrian_online_id' => $antrian->antrian_online_id,
            'nomor_antrian' => $antrian->nomor_antrian,
            'status_antrian' => $antrian->status_antrian,
            'nik' => self::maskNik($antrian->nik),
            'nama_lengkap' => $antrian->nama_lengkap,
            'alamat' => $antrian->alamat,
            'updated_at' => optional($antrian->updated_at)->toIso8601String(),
        ];
    }

    public static function maskNik(?string $nik): ?string
    {
        if ($nik === null || $nik === '') {
            return $nik;
        }

        if (strlen($nik) < 6) {
            return str_repeat('*', strlen($nik));
        }

        return substr($nik, 0, 6).str_repeat('*', max(0, strlen($nik) - 6));
    }

    /**
     * Mock flow — dipakai saat GCP_MOCK_ENABLED=true.
     *
     * Alur:
     *  - Terima file KTP (hanya untuk log; tidak dikirim keluar).
     *  - Generate fake file_id & gcs_path.
     *  - Schedule penulisan data OCR sintetis ke DB lewat
     *    app()->terminating() sehingga berjalan SETELAH response
     *    dikirim ke klien. Ini mensimulasi sifat asinkron Pub/Sub.
     *
     * @return array{status:string,file_id:?string,antrian_id:string,gcs_path:?string,raw:array<string,mixed>}
     */
    private function mockUpload(string $antrianId, UploadedFile $file): array
    {
        $fileId = bin2hex(random_bytes(16));
        $delay = max(0, (int) config('services.gcp_ktp.mock_delay_seconds', 2));

        Log::info('KtpOcrService[MOCK]: simulating OCR pipeline', [
            'antrian_online_id' => $antrianId,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'delay_seconds' => $delay,
        ]);

        app()->terminating(function () use ($antrianId, $delay, $file): void {
            try {
                if ($delay > 0) {
                    sleep($delay);
                }

                $payload = self::tryResolveMockFixturePayload($file, $antrianId) ?? self::generateMockPayload($antrianId);
                $this->handleWebhookPayload($payload);

                Log::info('KtpOcrService[MOCK]: payload applied', [
                    'antrian_online_id' => $antrianId,
                    'source' => ($payload['_mock_source'] ?? 'synthetic'),
                    'nik' => ($payload['nik'] ?? '') ? ('***'.substr((string) $payload['nik'], -4)) : '',
                ]);
            } catch (\Throwable $e) {
                Log::error('KtpOcrService[MOCK]: gagal menulis mock payload', [
                    'antrian_online_id' => $antrianId,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        return [
            'status' => 'uploaded',
            'file_id' => $fileId,
            'antrian_id' => $antrianId,
            'gcs_path' => "mock://uploaded_ktp/{$antrianId}/{$fileId}",
            'raw' => ['mock' => true, 'delay_seconds' => $delay],
        ];
    }

    /**
     * Data OCR sintetis yang konsisten per antrian_online_id.
     *
     * @return array<string, mixed>
     */
    public static function generateMockPayload(string $antrianId): array
    {
        $seed = abs(crc32($antrianId));
        $tail = str_pad((string) ($seed % 10000), 4, '0', STR_PAD_LEFT);
        $mid = str_pad((string) (($seed / 10) % 1000000), 6, '0', STR_PAD_LEFT);
        $nik = '31'.'71'.$mid.$tail;
        if (strlen($nik) !== 16) {
            $nik = str_pad(substr($nik, 0, 16), 16, '0');
        }

        $namaPool = ['SYAHRIAL AFFANDY', 'SITI RAHMAWATI', 'BUDI SANTOSO', 'DEWI KUSUMA', 'AGUS PRATAMA', 'RINI LESTARI'];
        $nama = $namaPool[$seed % count($namaPool)];

        return [
            'antrian_online_id' => $antrianId,
            'nik' => $nik,
            'nama_lengkap' => $nama,
            'alamat' => sprintf('Jl. Demo No. %d, RT 001/002, Kel. Mock, Kec. Mock', ($seed % 99) + 1),
            'confidence' => 0.95,
            '_mock_source' => 'synthetic',
        ];
    }

    /**
     * Saat GCP_MOCK_ENABLED: baca model/dataset/Test/{basename}.json (atau GCP_MOCK_DATASET_DIR)
     * agar hasil OCR uji sesuai data KTP Anda, bukan random dari UUID.
     *
     * Format JSON minimal:
     * { "nik": "16 digit", "nama_lengkap": "...", "alamat": "..." }
     *
     * Opsional: "confidence", "field_confidence"
     *
     * @return array<string, mixed>|null
     */
    public static function tryResolveMockFixturePayload(UploadedFile $file, string $antrianId): ?array
    {
        $dir = (string) config('services.gcp_ktp.mock_dataset_dir');
        if ($dir === '') {
            $dir = base_path('model'.DIRECTORY_SEPARATOR.'dataset'.DIRECTORY_SEPARATOR.'Test');
        }
        $resolved = realpath($dir);
        if ($resolved === false || ! is_dir($resolved)) {
            return null;
        }

        $original = (string) ($file->getClientOriginalName() ?: 'ktp.jpg');
        $stem = pathinfo($original, PATHINFO_FILENAME);
        $stem = preg_replace('/[^a-zA-Z0-9._\-]/', '_', (string) $stem) ?? '';
        if ($stem === '') {
            return null;
        }

        $path = $resolved.DIRECTORY_SEPARATOR.$stem.'.json';
        if (! is_file($path)) {
            Log::info('KtpOcrService[MOCK]: tidak ada fixture JSON, pakai data sintetis', [
                'cari_file' => $path,
                'nama_unggahan' => $original,
            ]);

            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            Log::warning('KtpOcrService[MOCK]: fixture JSON kosong/tidak terbaca', ['path' => $path]);

            return null;
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            Log::warning('KtpOcrService[MOCK]: fixture JSON tidak valid', ['path' => $path]);

            return null;
        }

        $nik = isset($data['nik']) ? preg_replace('/\D/', '', (string) $data['nik']) : '';
        if (strlen($nik) !== 16) {
            Log::warning('KtpOcrService[MOCK]: fixture NIK harus 16 digit angka', ['path' => $path, 'nik_len' => strlen($nik)]);

            return null;
        }

        $nama = isset($data['nama_lengkap']) ? trim((string) $data['nama_lengkap']) : '';
        if ($nama === '') {
            Log::warning('KtpOcrService[MOCK]: fixture nama_lengkap wajib diisi', ['path' => $path]);

            return null;
        }

        $alamat = isset($data['alamat']) ? trim((string) $data['alamat']) : '';
        if ($alamat === '') {
            Log::warning('KtpOcrService[MOCK]: fixture alamat wajib diisi', ['path' => $path]);

            return null;
        }

        $confidence = isset($data['confidence']) ? (float) $data['confidence'] : 0.99;
        $fieldConfidence = $data['field_confidence'] ?? null;
        if (! is_array($fieldConfidence)) {
            $fieldConfidence = [
                'nik' => 1.0,
                'nama_lengkap' => 1.0,
                'alamat' => 1.0,
            ];
        }

        Log::info('KtpOcrService[MOCK]: memakai fixture dataset', ['path' => $path]);

        return [
            'antrian_online_id' => $antrianId,
            'nik' => $nik,
            'nama_lengkap' => mb_substr($nama, 0, 100),
            'alamat' => $alamat,
            'confidence' => $confidence,
            'field_confidence' => $fieldConfidence,
            '_mock_source' => 'dataset_fixture',
        ];
    }
}
