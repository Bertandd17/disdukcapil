<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOcrJob;
use App\Services\EasyOcrService;
use App\Services\AdvancedKtpOcrParsingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * OcrController - API Controller for KTP OCR
 *
 * Endpoints:
 * - POST /api/ocr/upload - Upload KTP image for OCR processing
 * - GET /api/ocr/status/{id} - Check OCR processing status
 * - GET /api/ocr/result/{id} - Get OCR result
 * - POST /api/ocr/batch - Batch processing multiple images
 *
 * Disdukcapil Project - Anggota 5
 */
class OcrController extends Controller
{
    public function __construct(
        private EasyOcrService $ocrService,
        private AdvancedKtpOcrParsingService $parsingService
    ) {}

    /**
     * Upload and process KTP image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:10240', // Max 10MB
            'async' => 'sometimes|boolean',
            'antrian_id' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');
        $async = $request->boolean('async', true);
        $antrianId = $request->input('antrian_id') ?? Str::uuid()->toString();

        try {
            if ($async) {
                // Process asynchronously via queue
                ProcessOcrJob::dispatch(
                    $file,
                    $antrianId,
                    $request->ip()
                );

                Log::info('OCR job dispatched', ['antrian_id' => $antrianId]);

                return response()->json([
                    'success' => true,
                    'message' => 'OCR processing started',
                    'data' => [
                        'antrian_id' => $antrianId,
                        'status' => 'processing',
                        'check_url' => "/api/ocr/status/{$antrianId}",
                    ],
                ], 202);
            }

            // Process synchronously
            $result = $this->ocrService->processKtpImage($file, $antrianId);

            if ($result['success']) {
                // Parse the raw OCR text
                $parsed = $this->parsingService->parse($result['raw_text']);

                return response()->json([
                    'success' => true,
                    'message' => 'OCR completed successfully',
                    'data' => [
                        'antrian_id' => $antrianId,
                        'raw_text' => $result['raw_text'],
                        'parsed_data' => $parsed['data'] ?? [],
                        'confidence' => $parsed['confidence'] ?? $result['confidence'],
                        'field_confidence' => $parsed['field_confidence'] ?? [],
                        'processing_time' => $result['processing_time'],
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'OCR processing failed',
            ], 500);

        } catch (\Throwable $e) {
            Log::error('OCR processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check OCR processing status
     *
     * @param string $id
     * @return JsonResponse
     */
    public function status(string $id): JsonResponse
    {
        // Check cache for status
        $cached = cache()->get("ocr_status:{$id}");

        if ($cached) {
            return response()->json([
                'success' => true,
                'data' => $cached,
            ]);
        }

        // Check database if not in cache
        $antrian = \App\Models\AntrianOnline::find($id);

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'OCR record not found',
            ], 404);
        }

        $status = [
            'antrian_id' => $id,
            'status' => $antrian->status ?? 'unknown',
            'has_ocr_result' => !empty($antrian->ocr_raw_text),
            'confidence' => $antrian->ocr_confidence,
        ];

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Get OCR result
     *
     * @param string $id
     * @return JsonResponse
     */
    public function result(string $id): JsonResponse
    {
        $antrian = \App\Models\AntrianOnline::find($id);

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'OCR record not found',
            ], 404);
        }

        if (empty($antrian->ocr_raw_text)) {
            return response()->json([
                'success' => false,
                'message' => 'OCR result not available',
                'status' => $antrian->status,
            ], 404);
        }

        // Re-parse if needed or get cached result
        $parsed = cache()->remember("ocr_result:{$id}", 3600, function () use ($antrian) {
            return $this->parsingService->parse($antrian->ocr_raw_text);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'antrian_id' => $id,
                'parsed_data' => $parsed['data'] ?? [],
                'confidence' => $parsed['confidence'],
                'field_confidence' => $parsed['field_confidence'],
                'raw_text' => $antrian->ocr_raw_text,
                'processed_at' => $antrian->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Batch process multiple images
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function batch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|max:10240',
            'callback_url' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $batchId = Str::uuid()->toString();
        $results = [];
        $callbackUrl = $request->input('callback_url');

        foreach ($request->file('images') as $index => $file) {
            $antrianId = "{$batchId}-{$index}";

            ProcessOcrJob::dispatch(
                $file,
                $antrianId,
                $request->ip(),
                $callbackUrl ? "{$callbackUrl}?batch_id={$batchId}&index={$index}" : null
            );

            $results[] = [
                'index' => $index,
                'antrian_id' => $antrianId,
                'status' => 'processing',
            ];
        }

        // Store batch info
        cache()->put("ocr_batch:{$batchId}", [
            'batch_id' => $batchId,
            'total' => count($results),
            'processed' => 0,
            'results' => $results,
        ], 3600);

        return response()->json([
            'success' => true,
            'message' => 'Batch OCR processing started',
            'data' => [
                'batch_id' => $batchId,
                'total_images' => count($results),
                'items' => $results,
                'status_url' => "/api/ocr/batch/{$batchId}",
            ],
        ], 202);
    }

    /**
     * Get batch processing status
     *
     * @param string $batchId
     * @return JsonResponse
     */
    public function batchStatus(string $batchId): JsonResponse
    {
        $batch = cache()->get("ocr_batch:{$batchId}");

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found or expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $batch,
        ]);
    }

    /**
     * Health check for OCR service
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        $diagnostics = $this->ocrService->diagnose();

        $isHealthy = $diagnostics['python_found'] && $diagnostics['script_exists'];

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $isHealthy ? 'healthy' : 'unhealthy',
                'python' => [
                    'found' => $diagnostics['python_found'],
                    'version' => $diagnostics['python_version'] ?? null,
                ],
                'script' => [
                    'exists' => $diagnostics['script_exists'],
                    'path' => $diagnostics['script_path'] ?? null,
                ],
                'api_mode' => $diagnostics['api_mode'] ?? false,
            ],
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Test OCR with sample image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function test(Request $request): JsonResponse
    {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No image provided',
            ], 400);
        }

        $startTime = microtime(true);

        try {
            $result = $this->ocrService->processKtpImage($request->file('image'));

            $processingTime = microtime(true) - $startTime;

            return response()->json([
                'success' => true,
                'data' => [
                    'ocr_result' => $result,
                    'processing_time' => round($processingTime, 2),
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Reprocess OCR with different settings
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function reprocess(Request $request, string $id): JsonResponse
    {
        $antrian = \App\Models\AntrianOnline::find($id);

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        // Check if original file still exists
        if (!$antrian->file_ktp_path || !file_exists(storage_path('app/' . $antrian->file_ktp_path))) {
            return response()->json([
                'success' => false,
                'message' => 'Original file not found',
            ], 404);
        }

        // Dispatch reprocessing job
        ProcessOcrJob::dispatch(
            null,
            $id,
            $request->ip(),
            reprocess: true
        );

        return response()->json([
            'success' => true,
            'message' => 'OCR reprocessing started',
            'data' => [
                'antrian_id' => $id,
                'status' => 'reprocessing',
            ],
        ], 202);
    }
}
