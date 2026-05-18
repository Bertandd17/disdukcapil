<?php

namespace App\Services;

class XSSProtectionService
{
    /**
     * Clean string from XSS patterns
     *
     * @param  string  $input
     * @return string
     */
    public function clean(string $input): string
    {
        if (empty($input)) {
            return $input;
        }

        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Remove potentially dangerous HTML/JS
        $patterns = [
            // Script tags and content
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<script\b[^>]*>/i',

            // iframe tags
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',

            // object tags
            '/<object\b[^>]*>(.*?)<\/object>/is',

            // embed tags
            '/<embed\b[^>]*>/i',

            // applet tags
            '/<applet\b[^>]*>(.*?)<\/applet>/is',

            // meta tags (refresh, etc)
            '/<meta\b[^>]*>/i',

            // link tags
            '/<link\b[^>]*>/i',

            // style tags
            '/<style\b[^>]*>(.*?)<\/style>/is',

            // Event handlers (onclick, onerror, etc)
            '/on\w+\s*=\s*["\'](.*?)["\']?/i',

            // JavaScript protocols
            '/javascript:/i',
            '/vbscript:/i',
            '/data:text\/html/i',

            // PHP tags
            '/<\?php/i',
            '/<\?/i',
            '/\?>/i',

            // ASP tags
            '/<%/i',
            '/%>/i',

            // SSI
            '/<!--#/i',

            // Base64 encoded images with potential XSS
            '/<img[^>]+src=["\']data:image\/[^>]*>/i',

            // Expression in CSS
            '/expression\s*\(/i',

            // Behavior in CSS
            '/behavior\s*:/i',

            // @import in CSS
            '/@import/i',

            // -moz-binding
            '/-moz-binding\s*:/i',
        ];

        $cleaned = $input;

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }

        // Convert HTML entities
        $cleaned = htmlspecialchars($cleaned, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $cleaned;
    }

    /**
     * Clean array recursively
     *
     * @param  array  $data
     * @return array
     */
    public function cleanArray(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$this->clean($key)] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                $cleaned[$this->clean($key)] = $this->clean($value);
            } else {
                $cleaned[$this->clean($key)] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Detect XSS attempt
     *
     * @param  string  $input
     * @return bool
     */
    public function detect(string $input): bool
    {
        $patterns = [
            '/<script\b/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/data:text\/html/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize HTML (if HTML input is allowed)
     *
     * @param  string  $html
     * @param  array  $allowedTags
     * @return string
     */
    public function sanitizeHtml(string $html, array $allowedTags = []): string
    {
        // If no tags allowed, return escaped string
        if (empty($allowedTags)) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Strip all tags except allowed ones
        $cleaned = strip_tags($html, $allowedTags);

        // Remove attributes from allowed tags
        $cleaned = preg_replace('/<([a-z]+)[^>]*>/i', '<$1>', $cleaned);

        return $cleaned;
    }

    /**
     * Clean URL to prevent XSS
     *
     * @param  string  $url
     * @return string
     */
    public function cleanUrl(string $url): string
    {
        // Remove dangerous protocols
        $url = preg_replace('/^(javascript|vbscript|data):/i', '', $url);

        // Encode special characters
        $url = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $url;
    }

    /**
     * Validate and sanitize input
     *
     * @param  mixed  $input
     * @return mixed
     */
    public function sanitize($input)
    {
        if (is_string($input)) {
            return $this->clean($input);
        }

        if (is_array($input)) {
            return $this->cleanArray($input);
        }

        return $input;
    }

    /**
     * Log XSS attempt
     *
     * @param  string  $input
     * @param  string  $context
     * @return void
     */
    public function logXSSAttempt(string $input, string $context = 'unknown'): void
    {
        \Log::warning('XSS attempt detected and blocked', [
            'input' => substr($input, 0, 200), // Truncate for log
            'context' => $context,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Check for reflected XSS patterns
     *
     * @param  string  $input
     * @return bool
     */
    public function isReflectedXSS(string $input): bool
    {
        $reflectedPatterns = [
            '/<script[^>]*>.*?(document\.|location\.|window\.)/i',
            '/<img[^>]*src=["\']javascript:/i',
            '/<body[^>]*onload=/i',
            '/onerror\s*=/i',
        ];

        foreach ($reflectedPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate safe HTML attribute value
     *
     * @param  string  $value
     * @return string
     */
    public function safeAttribute(string $value): string
    {
        // Escape quotes and special characters
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove any remaining dangerous patterns
        $value = preg_replace('/javascript:/i', '', $value);

        return $value;
    }
}
