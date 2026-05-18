<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class XSSProtectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('security.xss.enabled', true)) {
            return $next($request);
        }

        // Sanitize input data
        $input = $request->all();

        if ($this->sanitizeInput($input, $request)) {
            $request->merge($input);
        }

        return $next($request);
    }

    /**
     * Sanitize input data to prevent XSS attacks
     *
     * @param  array  $input
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function sanitizeInput(array &$input, Request $request): bool
    {
        $sanitized = false;

        foreach ($input as $key => $value) {
            // Skip file uploads
            if ($request->hasFile($key)) {
                continue;
            }

            if (is_array($value)) {
                if ($this->sanitizeInput($value, $request)) {
                    $input[$key] = $value;
                    $sanitized = true;
                }
            } elseif (is_string($value)) {
                $cleaned = $this->cleanString($value);
                if ($cleaned !== $value) {
                    $input[$key] = $cleaned;
                    $sanitized = true;

                    // Log potential XSS attempt
                    if (config('security.audit.enabled', true)) {
                        \Log::warning('Potensi XSS attack terdeteksi dan dibersihkan', [
                            'input_key' => $key,
                            'original_value' => substr($value, 0, 100),
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                    }
                }
            }
        }

        return $sanitized;
    }

    /**
     * Clean string from XSS patterns
     *
     * @param  string  $string
     * @return string
     */
    private function cleanString(string $string): string
    {
        // List of dangerous patterns
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<object\b[^>]*>(.*?)<\/object>/is',
            '/<embed\b[^>]*>/is',
            '/<applet\b[^>]*>(.*?)<\/applet>/is',
            '/<meta\b[^>]*>/is',
            '/<link\b[^>]*>/is',
            '/<style\b[^>]*>(.*?)<\/style>/is',
            '/on\w+\s*=/i', // Event handlers like onclick, onerror, etc.
            '/javascript:/i',
            '/vbscript:/i',
            '/data:text\/html/i',
            '/<\?php/i',
            '/<\[/i',
        ];

        $cleaned = $string;

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }

        // Convert HTML entities
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $cleaned;
    }
}
