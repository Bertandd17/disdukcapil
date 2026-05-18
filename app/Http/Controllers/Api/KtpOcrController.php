<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\KtpOcrException;
use App\Http\Controllers\Controller;
use App\Http\Requests\KtpUploadRequest;
use App\Models\AntrianOnline;
use App\Services\KtpOcrService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KtpOcrController extends Controller
{
    public function __construct(private readonly KtpOcrService $service)
    {
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
