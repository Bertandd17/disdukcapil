<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SQLInjectionProtectionService
{
    /**
     * SQL injection patterns to detect
     *
     * @var array
     */
    protected $patterns = [
        // SQL comments
        '/--/',
        '/\/\*/',
        '/\*/',
        '/;/',

        // UNION SELECT
        '/\bUNION\b.*\bSELECT\b/i',
        '/\bUNION\s+ALL\b.*\bSELECT\b/i',

        // SELECT statements
        '/\bSELECT\b.*\bFROM\b/i',

        // DROP TABLE
        '/\bDROP\b.*\bTABLE\b/i',

        // INSERT INTO
        '/\bINSERT\b.*\bINTO\b/i',

        // DELETE FROM
        '/\bDELETE\b.*\bFROM\b/i',

        // UPDATE with SET
        '/\bUPDATE\b.*\bSET\b/i',

        // EXECUTE/EXEC
        '/\bEXEC\b|\bEXECUTE\b/i',

        // OR/AND with true conditions
        '/\b(OR|AND)\s+\d+\s*=\s*\d+/i',
        '/\b(OR|AND)\s+["\'].*["\']\s*=\s*["\'].*["\']/i',
        '/\b(OR|AND)\s+true\b/i',

        // Hex encoding
        '/0x[0-9a-fA-F]+/i',

        // CHAR function
        '/\bCHAR\s*\(/i',

        // WAITFOR DELAY (DoS)
        '/\bWAITFOR\b.*\bDELAY\b/i',

        // BENCHMARK (DoS)
        '/\bBENCHMARK\b/i',

        // SLEEP (DoS)
        '/\bSLEEP\b/i',

        // Information schema
        '/information_schema/i',

        // CONCAT with dangerous functions
        '/CONCAT\s*\(.*version\(/i',
        '/CONCAT\s*\(.*user\(\)/i',

        // Version and user functions
        '/@@version/i',
        '/@@servername/i',
        '/user\(\)/i',
        '/version\(\)/i',

        // XP_cmdshell (command execution)
        '/xp_cmdshell/i',

        // Sp_oacreate (COM execution)
        '/sp_oacreate/i',

        // INTO OUTFILE (file write)
        '/\bINTO\s+OUTFILE\b/i',

        // LOAD_FILE (file read)
        '/LOAD_FILE\s*\(/i',
    ];

    /**
     * Detect SQL injection attempt
     *
     * @param  string  $input
     * @param  string  $context
     * @return bool
     */
    public function detect(string $input, string $context = 'unknown'): bool
    {
        if (empty($input)) {
            return false;
        }

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logDetection($input, $context, $pattern);
                return true;
            }
        }

        return false;
    }

    /**
     * Detect SQL injection in array
     *
     * @param  array  $data
     * @param  string  $context
     * @return bool
     */
    public function detectInArray(array $data, string $context = 'unknown'): bool
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if ($this->detectInArray($value, $context . '.' . $key)) {
                    return true;
                }
            } elseif (is_string($value)) {
                if ($this->detect($value, $context . '.' . $key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Sanitize input from SQL injection patterns
     *
     * @param  string  $input
     * @return string
     */
    public function sanitize(string $input): string
    {
        $sanitized = $input;

        foreach ($this->patterns as $pattern) {
            $sanitized = preg_replace($pattern, '', $sanitized);
        }

        return $sanitized;
    }

    /**
     * Sanitize array recursively
     *
     * @param  array  $data
     * @return array
     */
    public function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Log SQL injection detection
     *
     * @param  string  $input
     * @param  string  $context
     * @param  string  $pattern
     * @return void
     */
    protected function logDetection(string $input, string $context, string $pattern): void
    {
        Log::warning('SQL injection attempt detected', [
            'input' => Str::limit($input, 200),
            'context' => $context,
            'pattern' => Str::limit($pattern, 100),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
        ]);
    }

    /**
     * Check for SQL injection in query string
     *
     * @param  string  $query
     * @return bool
     */
    public function checkQueryString(string $query): bool
    {
        // Check for common SQL injection patterns in query string
        $dangerousPatterns = [
            '/\bor\b.*?=.*?1/i',
            '/\band\b.*?=.*?1/i',
            '/\bunion\b.*\bselect\b/i',
            "/'.*or.*'1'='1/i",
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate order by clause
     *
     * @param  string  $column
     * @param  array  $allowedColumns
     * @return bool
     */
    public function validateOrderBy(string $column, array $allowedColumns): bool
    {
        return in_array($column, $allowedColumns);
    }

    /**
     * Validate direction (ASC/DESC)
     *
     * @param  string  $direction
     * @return bool
     */
    public function validateDirection(string $direction): bool
    {
        return in_array(strtoupper($direction), ['ASC', 'DESC']);
    }

    /**
     * Escape special characters for SQL
     *
     * @param  string  $value
     * @return string
     */
    public function escape(string $value): string
    {
        return addslashes($value);
    }

    /**
     * Validate LIKE clause
     *
     * @param  string  $pattern
     * @return bool
     */
    public function validateLikePattern(string $pattern): bool
    {
        // Check for dangerous characters in LIKE pattern
        $dangerousChars = [';', '--', '/*', '*/'];

        foreach ($dangerousChars as $char) {
            if (str_contains($pattern, $char)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check for time-based SQL injection
     *
     * @param  string  $input
     * @return bool
     */
    public function detectTimeBased(string $input): bool
    {
        $timeBasedPatterns = [
            '/waitfor\s+delay/i',
            '/sleep\s*\(/i',
            '/benchmark\s*\(/i',
            '/dbms_pipe\.receive_message/i',
            '/pg_sleep\s*\(/i',
        ];

        foreach ($timeBasedPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logDetection($input, 'time_based', $pattern);
                return true;
            }
        }

        return false;
    }

    /**
     * Check for blind SQL injection
     *
     * @param  string  $input
     * @return bool
     */
    public function detectBlind(string $input): bool
    {
        $blindPatterns = [
            '/and\s+\d+\s*=\s*\d+/i',
            '/or\s+\d+\s*=\s*\d+/i',
            '/and\s+1\s*=\s*1/i',
            '/or\s+1\s*=\s*1/i',
            "/and\s+'1'\s*=\s*'1/i",
            "/or\s+'1'\s*=\s*'1/i",
        ];

        foreach ($blindPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logDetection($input, 'blind', $pattern);
                return true;
            }
        }

        return false;
    }

    /**
     * Get safe LIKE pattern
     *
     * @param  string  $pattern
     * @return string
     */
    public function getSafeLikePattern(string $pattern): string
    {
        // Escape special characters
        $pattern = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $pattern);

        // Remove dangerous patterns
        $pattern = $this->sanitize($pattern);

        return $pattern;
    }
}
