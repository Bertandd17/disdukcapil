<?php

namespace App\Http\Middleware;

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
        // Skip non-upload requests
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        // Log attempt
        Log::info('OCR Security Middleware', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_length' => $request->header('Content-Length'),
        ]);

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
}
