<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AntrianOnline;
use App\Services\EasyOcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * EasyOcrController - API Controller untuk OCR KTP dengan EasyOCR
 * 
 * Endpoint:
 * - POST   /api/ocr/upload     - Upload dan proses gambar KTP
 * - GET    /api/ocr/status/{id} - Cek status hasil OCR
 * - GET    /api/ocr/result/{id} - Ambil hasil OCR
 * - POST   /api/ocr/batch     - Proses multiple images
 * 
 * Response format:
 * {
 *   "success": true,
 *   "message": "OCR berhasil diproses",
 *   "data": {
 *     "antrian_id": "uuid",
 *     "nik": "1271051708900001",
 *     "nama_lengkap": "BUDI SANTOSO",
 *     ...
 *   },
 *   "confidence": 0.95,
 *   "processing_time": 2.5
 * }
 */
class EasyOcrController extends Controller
{
    private EasyOcrService $ocrService;

    public function __construct(EasyOcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Upload dan proses gambar KTP
     *
     * POST /api/ocr/upload
     * 
     * @param  Request  $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'ktp_image' => [
                    'required',
                    'file',
                    'mimes:jpeg,png,jpg,jpeg2000',
                    'max:10240', // 10MB
                ],
                'antrian_id' => [
                    'nullable',
                    'uuid',
                ],
            ], [
                'ktp_image.required' => 'File gambar KTP wajib diunggah.',
                'ktp_image.image' => 'File harus berupa gambar.',
                'ktp_image.mimes' => 'Format file harus JPEG, PNG, atau JPG.',
                'ktp_image.max' => 'Ukuran file maksimal 10MB.',
            ]);

            $file = $request->file('ktp_image');
            $antrianId = $validated['antrian_id'] ?? null;

            Log::info('EasyOcrController: Upload request received', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'antrian_id' => $antrianId,
                'ip' => $request->ip(),
            ]);

            // Proses OCR
            $result = $this->ocrService->processKtpImage($file, $antrianId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            // Simpan hasil ke database
            $savedData = $this->saveOcrResult($result['data'], $antrianId, $file);

            return response()->json([
                'success' => true,
                'message' => 'KTP berhasil diproses.',
                'data' => [
                    'antrian_id' => $savedData['antrian_id'],
                    'nomor_antrian' => $savedData['nomor_antrian'],
                    'nik' => $result['data']['nik'] ?? '',
                    'nama_lengkap' => $result['data']['nama_lengkap'] ?? '',
                    'tempat_lahir' => $result['data']['tempat_lahir'] ?? '',
                    'tanggal_lahir' => $result['data']['tanggal_lahir'] ?? '',
                    'jenis_kelamin' => $result['data']['jenis_kelamin'] ?? '',
                    'gol_darah' => $result['data']['gol_darah'] ?? '',
                    'alamat' => $result['data']['alamat'] ?? '',
                    'rt_rw' => $result['data']['rt_rw'] ?? '',
                    'kel_desa' => $result['data']['kel_desa'] ?? '',
                    'kec' => $result['data']['kec'] ?? '',
                    'kab_kota' => $result['data']['kab_kota'] ?? '',
                    'provinsi' => $result['data']['provinsi'] ?? '',
                    'agama' => $result['data']['agama'] ?? '',
                    'status_perkawinan' => $result['data']['status_perkawinan'] ?? '',
                    'pekerjaan' => $result['data']['pekerjaan'] ?? '',
                    'kewarganegaraan' => $result['data']['kewarganegaraan'] ?? '',
                    'berlaku_hingga' => $result['data']['berlaku_hingga'] ?? '',
                ],
                'confidence' => $result['confidence'] ?? 0,
                'field_confidence' => $result['data']['field_confidence'] ?? [],
                'processing_time' => $result['processing_time'] ?? 0,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('EasyOcrController: Upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses KTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses multiple images
     *
     * POST /api/ocr/batch
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function batchUpload(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ktp_images' => [
                    'required',
                    'array',
                    'min:1',
                    'max:10',
                ],
                'ktp_images.*' => [
                    'file',
                    'mimes:jpeg,png,jpg,jpeg2000',
                    'max:10240',
                ],
            ], [
                'ktp_images.required' => 'Minimal satu gambar KTP wajib diunggah.',
                'ktp_images.*.file' => 'Setiap file harus berupa gambar.',
                'ktp_images.*.mimes' => 'Format file harus JPEG, PNG, atau JPG.',
            ]);

            $files = $request->file('ktp_images');
            
            Log::info('EasyOcrController: Batch upload request', [
                'file_count' => count($files),
                'ip' => $request->ip(),
            ]);

            $result = $this->ocrService->processMultipleImages($files);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? 'Semua gambar berhasil diproses.' 
                    : "Diproses dengan {$result['failed']} kegagalan.",
                'results' => $result['results'],
                'total_processed' => $result['total_processed'],
                'failed' => $result['failed'],
            ], $result['success'] ? 200 : 207);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            Log::error('EasyOcrController: Batch upload failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get status OCR result
     *
     * GET /api/ocr/status/{antrianId}
     *
     * @param  string  $antrianId
     * @return JsonResponse
     */
    public function status(string $antrianId): JsonResponse
    {
        try {
            // Cek cache
            $cached = $this->ocrService->getCachedResult($antrianId);
            
            if ($cached) {
                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'data' => $cached['data'] ?? [],
                    'confidence' => $cached['confidence'] ?? 0,
                    'processed_at' => $cached['processed_at'] ?? null,
                ]);
            }

            // Cek database
            $antrian = AntrianOnline::where('antrian_online_id', $antrianId)->first();

            if (!$antrian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Antrian tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status' => $antrian->status_antrian,
                'antrian_id' => $antrian->antrian_online_id,
                'nomor_antrian' => $antrian->nomor_antrian,
                'has_ocr_data' => !empty($antrian->nik) || !empty($antrian->nama_lengkap),
            ]);

        } catch (\Throwable $e) {
            Log::error('EasyOcrController: Status check failed', [
                'antrian_id' => $antrianId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
            ], 500);
        }
    }

    /**
     * Get OCR result
     *
     * GET /api/ocr/result/{antrianId}
     *
     * @param  string  $antrianId
     * @return JsonResponse
     */
    public function result(string $antrianId): JsonResponse
    {
        try {
            // Cek cache
            $cached = $this->ocrService->getCachedResult($antrianId);
            
            if ($cached) {
                return response()->json([
                    'success' => true,
                    'data' => $cached['data'] ?? [],
                    'raw_text' => $cached['raw_text'] ?? '',
                    'confidence' => $cached['confidence'] ?? 0,
                    'source' => 'cache',
                ]);
            }

            // Cek database
            $antrian = AntrianOnline::where('antrian_online_id', $antrianId)->first();

            if (!$antrian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            // Bangun response dari database
            $data = [
                'nik' => $antrian->nik ?? '',
                'nama_lengkap' => $antrian->nama_lengkap ?? '',
                'alamat' => $antrian->alamat ?? '',
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'source' => 'database',
            ]);

        } catch (\Throwable $e) {
            Log::error('EasyOcrController: Result fetch failed', [
                'antrian_id' => $antrianId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
            ], 500);
        }
    }

    /**
     * Save OCR result to database
     *
     * @param  array  $ocrData
     * @param  string|null  $antrianId
     * @param  mixed  $file
     * @return array
     */
    private function saveOcrResult(array $ocrData, ?string $antrianId, $file): array
    {
        return DB::transaction(function () use ($ocrData, $antrianId, $file) {
            // Simpan file jika ada
            $filePath = null;
            if ($file) {
                $filePath = $this->saveFile($file, $antrianId);
            }

            // Update atau buat antrian
            if ($antrianId) {
                $antrian = AntrianOnline::where('antrian_online_id', $antrianId)->first();
                
                if ($antrian) {
                    $this->updateAntrian($antrian, $ocrData, $filePath);
                    
                    return [
                        'antrian_id' => $antrian->antrian_online_id,
                        'nomor_antrian' => $antrian->nomor_antrian,
                    ];
                }
            }

            // Buat antrian baru
            $antrian = $this->createAntrian($ocrData, $filePath);
            
            return [
                'antrian_id' => $antrian->antrian_online_id,
                'nomor_antrian' => $antrian->nomor_antrian,
            ];
        });
    }

    /**
     * Update existing antrian with OCR data
     *
     * @param  AntrianOnline  $antrian
     * @param  array  $ocrData
     * @param  string|null  $filePath
     * @return void
     */
    private function updateAntrian(AntrianOnline $antrian, array $ocrData, ?string $filePath): void
    {
        // Update NIK
        if (!empty($ocrData['nik'])) {
            $antrian->nik = $ocrData['nik'];
        }

        // Update nama
        if (!empty($ocrData['nama_lengkap'])) {
            $antrian->nama_lengkap = $ocrData['nama_lengkap'];
        }

        // Update alamat
        if (!empty($ocrData['alamat'])) {
            $alamat = $ocrData['alamat'];
            
            // Gabungkan dengan komponen lain jika ada
            $parts = array_filter([
                $alamat,
                $ocrData['rt_rw'] ?? null,
                $ocrData['kel_desa'] ?? null,
                $ocrData['kec'] ?? null,
            ]);
            
            $antrian->alamat = implode(', ', $parts);
        }

        // Update status
        $hasData = !empty($ocrData['nik']) || !empty($ocrData['nama_lengkap']);
        if ($hasData) {
            $antrian->status_antrian = AntrianOnline::STATUS_DOKUMEN_DITERIMA;
        }

        // Update file path jika ada
        if ($filePath) {
            $antrian->file_ktp_path = $filePath;
        }

        $antrian->save();

        Log::info('EasyOcrController: Antrian updated', [
            'antrian_id' => $antrian->antrian_online_id,
            'has_nik' => !empty($ocrData['nik']),
            'has_nama' => !empty($ocrData['nama_lengkap']),
        ]);
    }

    /**
     * Create new antrian with OCR data
     *
     * @param  array  $ocrData
     * @param  string|null  $filePath
     * @return AntrianOnline
     */
    private function createAntrian(array $ocrData, ?string $filePath): AntrianOnline
    {
        $antrian = new AntrianOnline();
        $antrian->antrian_online_id = (string) Str::uuid();
        $antrian->nomor_antrian = $this->generateNomorAntrian();
        $antrian->layanan_id = $this->getDefaultLayananId();
        
        if (!empty($ocrData['nik'])) {
            $antrian->nik = $ocrData['nik'];
        }

        if (!empty($ocrData['nama_lengkap'])) {
            $antrian->nama_lengkap = $ocrData['nama_lengkap'];
        }

        if (!empty($ocrData['alamat'])) {
            $parts = array_filter([
                $ocrData['alamat'],
                $ocrData['rt_rw'] ?? null,
                $ocrData['kel_desa'] ?? null,
                $ocrData['kec'] ?? null,
            ]);
            $antrian->alamat = implode(', ', $parts);
        }

        $hasData = !empty($ocrData['nik']) || !empty($ocrData['nama_lengkap']);
        $antrian->status_antrian = $hasData 
            ? AntrianOnline::STATUS_DOKUMEN_DITERIMA 
            : AntrianOnline::STATUS_MENUNGGU;

        if ($filePath) {
            $antrian->file_ktp_path = $filePath;
        }

        $antrian->save();

        Log::info('EasyOcrController: New antrian created', [
            'antrian_id' => $antrian->antrian_online_id,
            'nomor_antrian' => $antrian->nomor_antrian,
        ]);

        return $antrian;
    }

    /**
     * Save uploaded file
     *
     * @param  mixed  $file
     * @param  string|null  $antrianId
     * @return string|null
     */
    private function saveFile($file, ?string $antrianId): ?string
    {
        try {
            $dir = 'ktp-uploads/' . ($antrianId ?? 'temp');
            
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            $path = Storage::putFileAs($dir, $file, $filename);
            
            return $path;
        } catch (\Throwable $e) {
            Log::warning('EasyOcrController: Failed to save file', [
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Generate unique antrian number
     *
     * @return string
     */
    private function generateNomorAntrian(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'ANT';
        
        $lastAntrian = AntrianOnline::whereDate('created_at', now()->toDateString())
            ->where('nomor_antrian', 'like', "{$prefix}{$date}%")
            ->orderBy('nomor_antrian', 'desc')
            ->first();

        if ($lastAntrian) {
            $lastNumber = (int) substr($lastAntrian->nomor_antrian, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad((string) $newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get default layanan ID
     *
     * @return string|null
     */
    private function getDefaultLayananId(): ?string
    {
        $layanan = \App\Models\Layanan_Model::first();
        return $layanan?->layanan_id;
    }
}
