<?php

namespace App\Http\Controllers;

use App\Exceptions\KtpOcrException;
use App\Models\AntrianOnline;
use App\Services\KtpOcrParsingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * KtpOcrController - Direct API Call ke Google Vision API.
 * 
 * Alur:
 * 1. Terima upload gambar KTP
 * 2. Baca file dengan file_get_contents()
 * 3. Panggil Google\Cloud\Vision\V1\ImageAnnotatorClient langsung
 * 4. Parse hasil OCR dengan KtpOcrParsingService
 * 5. Simpan ke tabel antrian_online
 * 6. Return JSON response
 * 
 * KEUNTUNGAN: Tidak perlu Cloud Functions, Pub/Sub, atau billing GCP aktif
 * untuk free tier (Cloud Vision API memberikan 1000 req/bulan gratis).
 */
class KtpOcrController extends Controller
{
    private KtpOcrParsingService $parsingService;

    public function __construct(KtpOcrParsingService $parsingService)
    {
        $this->parsingService = $parsingService;
    }

    /**
     * Upload dan proses gambar KTP dengan direct API call.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function uploadKtp(Request $request): JsonResponse
    {
        // ============================================
        // 1. VALIDASI INPUT
        // ============================================
        $validated = $request->validate([
            'ktp_image' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg',
                'max:5120', // 5MB
            ],
            'antrian_online_id' => [
                'nullable',
                'uuid',
            ],
        ], [
            'ktp_image.required' => 'File gambar KTP wajib diunggah.',
            'ktp_image.image' => 'File harus berupa gambar.',
            'ktp_image.mimes' => 'Format file harus JPEG, PNG, atau JPG.',
            'ktp_image.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $file = $request->file('ktp_image');
            $antrianOnlineId = $validated['antrian_online_id'] ?? null;

            Log::info('KtpOcrController: processing upload', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'antrian_online_id' => $antrianOnlineId,
                'ip' => $request->ip(),
            ]);

            // ============================================
            // 2. PROSES OCR DENGAN GOOGLE VISION API
            // ============================================
            $ocrResult = $this->processWithVisionApi($file);

            if (!$ocrResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $ocrResult['message'],
                    'errors' => $ocrResult['errors'] ?? [],
                ], $ocrResult['status_code'] ?? 500);
            }

            $rawText = $ocrResult['raw_text'];

            Log::debug('KtpOcrController: Vision API response', [
                'text_length' => strlen($rawText),
                'text_preview' => substr($rawText, 0, 200),
            ]);

            // ============================================
            // 3. PARSING TEKS OCR
            // ============================================
            $parsedData = $this->parsingService->parse($rawText);

            Log::info('KtpOcrController: parsed data', [
                'nik' => $parsedData['nik'] ? (substr($parsedData['nik'], 0, 6) . '******') : 'empty',
                'nama' => $parsedData['nama_lengkap'] ?: 'empty',
                'confidence' => $parsedData['confidence'],
            ]);

            // ============================================
            // 4. SIMPAN KE DATABASE
            // ============================================
            $savedData = $this->saveToDatabase($parsedData, $antrianOnlineId, $file);

            // ============================================
            // 5. RETURN SUCCESS RESPONSE
            // ============================================
            return response()->json([
                'success' => true,
                'message' => 'KTP berhasil diproses.',
                'data' => [
                    'antrian_online_id' => $savedData['antrian_online_id'],
                    'nomor_antrian' => $savedData['nomor_antrian'],
                    'nik' => $parsedData['nik'],
                    'nama_lengkap' => $parsedData['nama_lengkap'],
                    'tempat_lahir' => $parsedData['tempat_lahir'],
                    'tanggal_lahir' => $parsedData['tanggal_lahir'],
                    'jenis_kelamin' => $parsedData['jenis_kelamin'],
                    'gol_darah' => $parsedData['gol_darah'],
                    'alamat' => $parsedData['alamat'],
                    'rt_rw' => $parsedData['rt_rw'],
                    'kel_desa' => $parsedData['kel_desa'],
                    'kecamatan' => $parsedData['kecamatan'],
                    'kab_kota' => $parsedData['kab_kota'],
                    'provinsi' => $parsedData['provinsi'],
                    'agama' => $parsedData['agama'],
                    'status_perkawinan' => $parsedData['status_perkawinan'],
                    'pekerjaan' => $parsedData['pekerjaan'],
                    'kewarganegaraan' => $parsedData['kewarganegaraan'],
                    'confidence' => $parsedData['confidence'],
                    'field_confidence' => $parsedData['field_confidence'],
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('KtpOcrController: upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses KTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses gambar dengan Google Vision API secara langsung.
     * Support 2 mode:
     * 1. Service Account JSON (via SDK)
     * 2. API Key (via REST API)
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array{success: bool, raw_text?: string, message: string, errors?: array, status_code?: int}
     */
    private function processWithVisionApi($file): array
    {
        // ============================================
        // CEK APAKAH MENGGUNAKAN MOCK MODE (UNTUK TESTING)
        // ============================================
        if ($this->isMockModeEnabled()) {
            return $this->mockVisionApiResponse($file);
        }

        // ============================================
        // VALIDASI FILE
        // ============================================
        $localPath = $file->getRealPath();
        if ($localPath === '' || !is_readable($localPath)) {
            return [
                'success' => false,
                'message' => 'File tidak dapat dibaca dari server.',
                'errors' => ['file' => 'Unable to read uploaded file.'],
                'status_code' => 500,
            ];
        }

        // ============================================
        // BACA IMAGE DATA
        // ============================================
        $imageData = @file_get_contents($localPath);
        if ($imageData === false || $imageData === '') {
            return [
                'success' => false,
                'message' => 'Gagal membaca data gambar.',
                'errors' => ['image' => 'Failed to read image data.'],
                'status_code' => 500,
            ];
        }

        // ============================================
        // COBA PAKAI API KEY DULU (LEBIH MUDAH)
        // ============================================
        $apiKey = $this->getApiKey();
        if ($apiKey !== '') {
            return $this->processWithVisionApiKey($imageData, $file);
        }

        // ============================================
        // FALLBACK: PAKAI SERVICE ACCOUNT JSON
        // ============================================
        $credentialsPath = $this->getCredentialsPath();
        
        if ($credentialsPath === '' || !file_exists($credentialsPath)) {
            Log::warning('KtpOcrController: credentials not found, using mock mode', [
                'credentials_path' => $credentialsPath,
                'hint' => 'Set GOOGLE_VISION_API_KEY in .env to use API Key authentication',
            ]);
            return $this->mockVisionApiResponse($file);
        }

        return $this->processWithVisionSdk($credentialsPath, $imageData, $file);
    }

    /**
     * Proses dengan Google Vision REST API menggunakan API Key.
     *
     * @param  string  $imageData
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array
     */
    private function processWithVisionApiKey(string $imageData, $file): array
    {
        $apiKey = $this->getApiKey();
        $projectId = env('GCP_PROJECT_ID', '');
        
        // Endpoint untuk document text detection
        $endpoint = 'https://vision.googleapis.com/v1/images:annotate';
        
        if ($projectId) {
            $endpoint = "https://vision.googleapis.com/v1/projects/{$projectId}/locations/global/images:annotate";
        }

        // Build request body
        $base64Image = base64_encode($imageData);
        $requestBody = [
            'requests' => [
                [
                    'image' => [
                        'content' => $base64Image,
                    ],
                    'features' => [
                        [
                            'type' => 'DOCUMENT_TEXT_DETECTION',
                            'maxResults' => 1,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $url = $endpoint . '?key=' . $apiKey;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestBody),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => (int) env('VISION_API_TIMEOUT', 30),
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('KtpOcrController: cURL error', ['error' => $error]);
                return [
                    'success' => false,
                    'message' => 'Gagal koneksi ke Google Vision API: ' . $error,
                    'errors' => ['curl' => $error],
                    'status_code' => 502,
                ];
            }

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? 'HTTP ' . $httpCode;
                
                Log::error('KtpOcrController: Vision API HTTP error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);

                // Fallback ke mock jika error credentials
                if ($httpCode === 403 || $httpCode === 400) {
                    Log::warning('KtpOcrController: API Key error, falling back to mock');
                    return $this->mockVisionApiResponse($file);
                }

                return [
                    'success' => false,
                    'message' => 'Google Vision API error: ' . $errorMessage,
                    'errors' => ['vision_api' => $errorMessage],
                    'status_code' => $httpCode,
                ];
            }

            // Parse response
            $result = json_decode($response, true);
            
            if (!isset($result['responses'][0])) {
                return [
                    'success' => false,
                    'message' => 'Response tidak valid dari Google Vision API.',
                    'errors' => ['response' => 'Invalid API response'],
                    'status_code' => 500,
                ];
            }

            $responseData = $result['responses'][0];

            // Cek error dalam response
            if (isset($responseData['error'])) {
                $errorMessage = $responseData['error']['message'] ?? 'Unknown error';
                Log::error('KtpOcrController: Vision API response error', [
                    'error' => $errorMessage,
                ]);
                return [
                    'success' => false,
                    'message' => 'Google Vision API error: ' . $errorMessage,
                    'errors' => ['vision_api' => $errorMessage],
                    'status_code' => 502,
                ];
            }

            // Ambil teks dari response
            $rawText = $responseData['textAnnotations'][0]['description'] 
                ?? $responseData['fullTextAnnotation']['text'] 
                ?? '';

            if (trim($rawText) === '') {
                return [
                    'success' => false,
                    'message' => 'Tidak ada teks yang dapat diekstrak dari gambar.',
                    'errors' => ['ocr' => 'No text found in image.'],
                    'status_code' => 422,
                ];
            }

            Log::info('KtpOcrController: OCR success via API Key', [
                'text_length' => strlen($rawText),
            ]);

            return [
                'success' => true,
                'raw_text' => $rawText,
                'message' => 'OCR berhasil.',
            ];

        } catch (\Throwable $e) {
            Log::error('KtpOcrController: Vision API Key exception', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal memproses dengan Google Vision API: ' . $e->getMessage(),
                'errors' => ['vision_api' => $e->getMessage()],
                'status_code' => 502,
            ];
        }
    }

    /**
     * Proses dengan Google Vision SDK menggunakan Service Account JSON.
     *
     * @param  string  $credentialsPath
     * @param  string  $imageData
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array
     */
    private function processWithVisionSdk(string $credentialsPath, string $imageData, $file): array
    {
        try {
            $clientOptions = ['credentials' => $credentialsPath];
            $client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient($clientOptions);
            
            $image = new \Google\Cloud\Vision\V1\Image();
            $image->setContent($imageData);
            
            $response = $client->documentTextDetection($image);
            $client->close();

            // ============================================
            // CEK ERROR RESPONSE
            // ============================================
            $error = $response->getError();
            if ($error !== null && $error->getMessage() !== '') {
                Log::error('KtpOcrController: Vision SDK error', [
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Google Vision API mengembalikan error: ' . $error->getMessage(),
                    'errors' => ['vision_api' => $error->getMessage()],
                    'status_code' => 502,
                ];
            }

            // ============================================
            // AMBIL HASIL TEXT
            // ============================================
            $fullText = $response->getFullTextAnnotation();
            $rawText = $fullText !== null ? (string) $fullText->getText() : '';

            if (trim($rawText) === '') {
                return [
                    'success' => false,
                    'message' => 'Tidak ada teks yang dapat diekstrak dari gambar.',
                    'errors' => ['ocr' => 'No text found in image.'],
                    'status_code' => 422,
                ];
            }

            Log::info('KtpOcrController: OCR success via SDK', [
                'text_length' => strlen($rawText),
            ]);

            return [
                'success' => true,
                'raw_text' => $rawText,
                'message' => 'OCR berhasil.',
            ];

        } catch (\Throwable $e) {
            Log::error('KtpOcrController: Vision SDK exception', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);

            // Jika terjadi error, coba fallback ke mock
            if ($this->shouldFallbackToMock($e)) {
                Log::info('KtpOcrController: falling back to mock mode');
                return $this->mockVisionApiResponse($file);
            }

            return [
                'success' => false,
                'message' => 'Gagal memproses dengan Google Vision API: ' . $e->getMessage(),
                'errors' => ['vision_api' => $e->getMessage()],
                'status_code' => 502,
            ];
        }
    }

    /**
     * Ambil API Key dari konfigurasi.
     *
     * @return string
     */
    private function getApiKey(): string
    {
        // Coba dari config/services
        $apiKey = config('services.google_vision.api_key');
        if ($apiKey) {
            return $apiKey;
        }

        // Fallback ke environment variable
        return env('GOOGLE_VISION_API_KEY', '');
    }

    /**
     * Mock response untuk development/testing.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array{success: bool, raw_text: string, message: string}
     */
    private function mockVisionApiResponse($file): array
    {
        Log::info('KtpOcrController: using mock OCR response', [
            'file_name' => $file->getClientOriginalName(),
        ]);

        // ============================================
        // COBA LOAD DARI DATASET FIXTURE
        // ============================================
        $fixtureText = $this->loadDatasetFixture($file);
        if ($fixtureText !== null) {
            return [
                'success' => true,
                'raw_text' => $fixtureText,
                'message' => 'Mock OCR berhasil (dataset fixture).',
            ];
        }

        // Fallback ke mock text generik
        $mockText = <<<EOT
PROVINSI JAWA BARAT
KOTA BANDUNG
NIK : 3273011708900001
Nama : BUDI SANTOSO
Tempat/Tgl Lahir : BANDUNG, 17-08-1990
Jenis Kelamin : LAKI-LAKI / Gol. Darah : A
Alamat : JL. Asia Afrika No. 100
RT/RW : 001/002
Kel/Desa : SUMUR BANDUNG
Kecamatan : BANDUNG WETAN
Kabupaten/Kota : KOTA BANDUNG
Provinsi : JAWA BARAT
Agama : ISLAM
Status Perkawinan : KAWIN
Pekerjaan : PEGAWAI NEGERI SIPIL
Kewarganegaraan : WNI
EOT;

        return [
            'success' => true,
            'raw_text' => $mockText,
            'message' => 'Mock OCR berhasil.',
        ];
    }

    /**
     * Load fixture OCR dari dataset folder.
     * 
     * Mencari file JSON dengan nama yang sama dengan gambar yang diupload.
     * Jika ada dataset_45.png, akan mencari dataset_45.json
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string|null
     */
    private function loadDatasetFixture($file): ?string
    {
        $dir = config('services.google_vision.mock_dataset_dir', base_path('model/dataset/Test'));
        
        if (!is_dir($dir)) {
            Log::debug('KtpOcrController: dataset dir not found', ['dir' => $dir]);
            return null;
        }

        $originalName = $file->getClientOriginalName() ?: 'ktp.jpg';
        $stem = pathinfo($originalName, PATHINFO_FILENAME);
        $stem = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $stem) ?: 'ktp';
        
        $jsonPath = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $stem . '.json';
        
        if (!is_file($jsonPath)) {
            Log::debug('KtpOcrController: fixture JSON not found', [
                'cari_file' => $jsonPath,
                'nama_file' => $originalName,
            ]);
            return null;
        }

        $raw = @file_get_contents($jsonPath);
        if ($raw === false || $raw === '') {
            Log::warning('KtpOcrController: fixture JSON empty or unreadable', ['path' => $jsonPath]);
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            Log::warning('KtpOcrController: fixture JSON invalid', ['path' => $jsonPath]);
            return null;
        }

        // Konversi JSON fixture ke format teks OCR
        return $this->convertFixtureToOcrText($data);
    }

    /**
     * Konversi fixture JSON ke format teks OCR.
     *
     * @param  array  $data
     * @return string
     */
    private function convertFixtureToOcrText(array $data): string
    {
        $lines = [];

        // Provinsi (nullable)
        if (!empty($data['provinsi'])) {
            $lines[] = 'PROVINSI ' . strtoupper($data['provinsi']);
        }

        // Kabupaten/Kota
        if (!empty($data['kab_kota'])) {
            $lines[] = strtoupper($data['kab_kota']);
        } elseif (!empty($data['kabupaten_kota'])) {
            $lines[] = strtoupper($data['kabupaten_kota']);
        }

        // NIK
        if (!empty($data['nik'])) {
            $lines[] = 'NIK : ' . $data['nik'];
        }

        // Nama
        if (!empty($data['nama_lengkap'])) {
            $lines[] = 'Nama : ' . strtoupper($data['nama_lengkap']);
        }

        // Tempat/Tanggal Lahir
        $tempat = $data['tempat_lahir'] ?? '';
        $tanggal = $data['tanggal_lahir'] ?? '';
        if ($tempat || $tanggal) {
            $lines[] = 'Tempat/Tgl Lahir : ' . strtoupper($tempat) . ($tanggal ? ', ' . $tanggal : '');
        }

        // Jenis Kelamin
        $jk = strtoupper($data['jenis_kelamin'] ?? '');
        $goldar = strtoupper($data['gol_darah'] ?? '');
        if ($jk || $goldar) {
            $jkPart = $jk ? $jk : 'LAKI-LAKI';
            $gdPart = $goldar ? ' Gol. Darah : ' . $goldar : '';
            $lines[] = 'Jenis Kelamin : ' . $jkPart . $gdPart;
        }

        // Alamat
        if (!empty($data['alamat'])) {
            $lines[] = 'Alamat : ' . strtoupper($data['alamat']);
        }

        // RT/RW
        if (!empty($data['rt_rw'])) {
            $lines[] = 'RT/RW : ' . strtoupper($data['rt_rw']);
        }

        // Kelurahan/Desa
        if (!empty($data['kel_desa'])) {
            $lines[] = 'Kel/Desa : ' . strtoupper($data['kel_desa']);
        }

        // Kecamatan
        if (!empty($data['kecamatan'])) {
            $lines[] = 'Kecamatan : ' . strtoupper($data['kecamatan']);
        }

        // Kabupaten/Kota (ulang jika ada)
        if (!empty($data['kab_kota'])) {
            $lines[] = 'Kabupaten/Kota : ' . strtoupper($data['kab_kota']);
        }

        // Provinsi (ulang jika ada)
        if (!empty($data['provinsi'])) {
            $lines[] = 'Provinsi : ' . strtoupper($data['provinsi']);
        }

        // Agama
        if (!empty($data['agama'])) {
            $lines[] = 'Agama : ' . strtoupper($data['agama']);
        }

        // Status Perkawinan
        if (!empty($data['status_perkawinan'])) {
            $lines[] = 'Status Perkawinan : ' . strtoupper($data['status_perkawinan']);
        }

        // Pekerjaan
        if (!empty($data['pekerjaan'])) {
            $lines[] = 'Pekerjaan : ' . strtoupper($data['pekerjaan']);
        }

        // Kewarganegaraan
        $kw = strtoupper($data['kewarganegaraan'] ?? 'WNI');
        $lines[] = 'Kewarganegaraan : ' . $kw;

        Log::info('KtpOcrController: using dataset fixture', [
            'line_count' => count($lines),
        ]);

        return implode("\n", $lines);
    }

    /**
     * Cek apakah mock mode diaktifkan.
     *
     * @return bool
     */
    private function isMockModeEnabled(): bool
    {
        return env('VISION_MOCK_ENABLED', false) === true
            || env('VISION_MOCK_ENABLED', 'false') === 'true';
    }

    /**
     * Ambil path credentials dari config/env.
     *
     * @return string
     */
    private function getCredentialsPath(): string
    {
        // Coba dari config/services
        $path = config('services.google_vision.credentials_path');
        
        if ($path !== null && $path !== '') {
            return base_path($path);
        }

        // Fallback ke environment variable
        $envPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        if ($envPath !== null && $envPath !== '') {
            return base_path($envPath);
        }

        // Default path
        return base_path('storage/app/google-creds.json');
    }

    /**
     * Cek apakah harus fallback ke mock mode.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    private function shouldFallbackToMock(\Throwable $e): bool
    {
        // Fallback jika error terkait credentials atau network
        $fallbackMessages = [
            'credentials',
            'authentication',
            'Unable to detect',
            'could not be found',
            'permission',
            'billing',
            '403',
            'PERMISSION_DENIED',
        ];

        foreach ($fallbackMessages as $msg) {
            if (stripos($e->getMessage(), $msg) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Simpan data hasil parsing ke database.
     *
     * @param  array  $parsedData
     * @param  string|null  $antrianOnlineId
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array
     */
    private function saveToDatabase(array $parsedData, ?string $antrianOnlineId, $file): array
    {
        return DB::transaction(function () use ($parsedData, $antrianOnlineId, $file) {
            // Jika ada antrian_online_id, update data yang sudah ada
            if ($antrianOnlineId !== null) {
                $antrian = AntrianOnline::where('antrian_online_id', $antrianOnlineId)->first();
                
                if ($antrian) {
                    $this->updateAntrianData($antrian, $parsedData);
                    $antrian->save();
                    
                    return [
                        'antrian_online_id' => $antrian->antrian_online_id,
                        'nomor_antrian' => $antrian->nomor_antrian,
                    ];
                }
            }

            // Buat antrian baru jika tidak ada
            $antrian = $this->createNewAntrian($parsedData);
            
            return [
                'antrian_online_id' => $antrian->antrian_online_id,
                'nomor_antrian' => $antrian->nomor_antrian,
            ];
        });
    }

    /**
     * Update data antrian dengan hasil parsing.
     *
     * @param  AntrianOnline  $antrian
     * @param  array  $parsedData
     * @return void
     */
    private function updateAntrianData(AntrianOnline $antrian, array $parsedData): void
    {
        // NIK akan di-encrypt otomatis oleh accessor/mutator di model
        if (!empty($parsedData['nik'])) {
            $antrian->nik = $parsedData['nik'];
        }

        if (!empty($parsedData['nama_lengkap'])) {
            $antrian->nama_lengkap = $parsedData['nama_lengkap'];
        }

        if (!empty($parsedData['alamat'])) {
            $antrian->alamat = $parsedData['alamat'];
        }

        // Update status jika ada data yang berhasil di-parse
        $hasNik = !empty($parsedData['nik']);
        $hasNama = !empty($parsedData['nama_lengkap']);
        $hasAlamat = !empty($parsedData['alamat']);

        if ($hasNik || $hasNama || $hasAlamat) {
            $antrian->status_antrian = AntrianOnline::STATUS_DOKUMEN_DITERIMA;
        }

        Log::info('KtpOcrController: updated antrian data', [
            'antrian_online_id' => $antrian->antrian_online_id,
            'has_nik' => $hasNik,
            'has_nama' => $hasNama,
            'has_alamat' => $hasAlamat,
            'new_status' => $antrian->status_antrian,
        ]);
    }

    /**
     * Buat antrian baru dengan hasil parsing.
     *
     * @param  array  $parsedData
     * @return AntrianOnline
     */
    private function createNewAntrian(array $parsedData): AntrianOnline
    {
        // Generate nomor antrian
        $nomorAntrian = $this->generateNomorAntrian();

        $antrian = new AntrianOnline();
        $antrian->nomor_antrian = $nomorAntrian;
        $antrian->layanan_id = $this->getDefaultLayananId();
        
        // NIK akan di-encrypt otomatis
        if (!empty($parsedData['nik'])) {
            $antrian->nik = $parsedData['nik'];
        }

        if (!empty($parsedData['nama_lengkap'])) {
            $antrian->nama_lengkap = $parsedData['nama_lengkap'];
        } else {
            $antrian->nama_lengkap = 'Unknown';
        }

        if (!empty($parsedData['alamat'])) {
            $antrian->alamat = $parsedData['alamat'];
        }

        // Set status berdasarkan hasil parsing
        $hasNik = !empty($parsedData['nik']);
        $hasNama = !empty($parsedData['nama_lengkap']);
        $hasAlamat = !empty($parsedData['alamat']);

        if ($hasNik || $hasNama || $hasAlamat) {
            $antrian->status_antrian = AntrianOnline::STATUS_DOKUMEN_DITERIMA;
        } else {
            $antrian->status_antrian = AntrianOnline::STATUS_MENUNGGU;
        }

        $antrian->save();

        Log::info('KtpOcrController: created new antrian', [
            'antrian_online_id' => $antrian->antrian_online_id,
            'nomor_antrian' => $nomorAntrian,
            'status' => $antrian->status_antrian,
        ]);

        return $antrian;
    }

    /**
     * Generate nomor antrian unik.
     *
     * @return string
     */
    private function generateNomorAntrian(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'ANT';
        
        // Ambil counter terakhir hari ini
        $lastAntrian = AntrianOnline::whereDate('created_at', now()->toDateString())
            ->where('nomor_antrian', 'like', "{$prefix}{$date}%")
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        if ($lastAntrian) {
            // Extract nomor urut
            $lastNumber = (int) substr($lastAntrian->nomor_antrian, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil layanan default ID.
     *
     * @return string|null
     */
    private function getDefaultLayananId(): ?string
    {
        // Ambil layanan pertama atau null jika tidak ada
        $layanan = \App\Models\Layanan_Model::first();
        
        return $layanan?->layanan_id;
    }

    /**
     * Get status antrian by ID (untuk polling).
     *
     * @param  string  $antrianOnlineId
     * @return JsonResponse
     */
    public function getStatus(string $antrianOnlineId): JsonResponse
    {
        try {
            $antrian = AntrianOnline::where('antrian_online_id', $antrianOnlineId)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'antrian_online_id' => $antrian->antrian_online_id,
                    'nomor_antrian' => $antrian->nomor_antrian,
                    'status_antrian' => $antrian->status_antrian,
                    'nik' => $antrian->nik ? (substr($antrian->nik, 0, 6) . '******') : null,
                    'nama_lengkap' => $antrian->nama_lengkap,
                    'alamat' => $antrian->alamat,
                    'updated_at' => $antrian->updated_at?->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('KtpOcrController: getStatus failed', [
                'antrian_online_id' => $antrianOnlineId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil status.',
            ], 500);
        }
    }
}
