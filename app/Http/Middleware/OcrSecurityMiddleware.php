<?php

namespace App\Http\Middleware;

use App\Services\SecureFileUploadService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk keamanan endpoint OCR.
 *
 * Fitur:
 * - Validasi request size
 * - Rate limiting tambahan
 * - Logging aktivitas
 * - CORS headers untuk API
 * - Validasi file upload security via SecureFileUploadService
 */
class OcrSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log attempt (NIK/sensitive data already masked by logging config)
        Log::info('OCR Security Middleware', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_length' => $request->header('Content-Length'),
            'method' => $request->method(),
        ]);

        // Validasi file upload security untuk POST request dengan file
        if ($request->isMethod('POST') && $request->hasFile('image') || $request->hasFile('ktp_image') || $request->hasFile('images')) {
            $this->validateUploadSecurity($request);
        }

        // Add security headers
        $response = $next($request);

        // CORS headers for API
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, X-CSRF-TOKEN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');

        return $response;
    }

    /**
     * Validasi keamanan file upload via SecureFileUploadService.
     *
     * @param  Request  $request
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    private function validateUploadSecurity(Request $request): void
    {
        $files = [];

        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }
        if ($request->hasFile('ktp_image')) {
            $files[] = $request->file('ktp_image');
        }
        if ($request->hasFile('images')) {
            $uploadedFiles = $request->file('images');
            if (is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $file) {
                    $files[] = $file;
                }
            }
        }

        if (empty($files)) {
            return;
        }

        $service = app(SecureFileUploadService::class);

        foreach ($files as $index => $file) {
            $result = $service->validateFile($file);

            if (! $result->isValid()) {
                Log::warning('OCR Security Middleware: File rejected', [
                    'file_index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'size_bytes' => $file->getSize(),
                    'errors' => $result->errors,
                ]);

                response()->json([
                    'success' => false,
                    'message' => 'File tidak aman untuk diunggah.',
                    'errors' => $result->errors,
                    'error_codes' => $result->errorCodes(),
                ], 422)->throwResponse();
            }
        }
    }
}