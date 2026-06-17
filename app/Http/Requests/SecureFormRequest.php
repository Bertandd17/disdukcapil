<?php

namespace App\Http\Requests;

use App\Services\XSSProtectionService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

class SecureFormRequest extends FormRequest
{
    /**
     * Pola XSS tambahan selain XSSProtectionService.
     */
    protected static array $xssPatterns = [
        '/<script\b[^>]*>.*?<\/script>/is',
        '/<[^>]+on\w+\s*=\s*["\'][^"\']*["\']/i',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/<iframe\b/i',
        '/<img[^>]+src\s*=\s*["\']?\s*javascript:/i',
        '/expression\s*\(/i',
        '/data\s*:\s*text\/html/i',
        '/&#x?[0-9a-f]+;?/i',
        '/<object\b/i',
        '/<embed\b/i',
        '/<svg\b/i',
        '/document\.(cookie|write|location)/i',
        '/window\.(location|open|alert|eval)/i',
        '/eval\s*\(/i',
        '/alert\s*\(/i',
        '/prompt\s*\(/i',
        '/confirm\s*\(/i',
        '/\bXST\b/i',
    ];

    /**
     * Pola SQL injection.
     */
    protected static array $sqliPatterns = [
        '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\s/i',
        '/--\s*$/m',
        '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
        '/\'\s*(OR|AND)\s*\'?\d+\'\s*=\s*\'\d+/i',
        '/\bOR\b\s+\d+\s*=\s*\d+/i',
        '/\'\s*(OR|AND)\s+\d+\s*=\s*\d+/i',
        '/SLEEP\s*\(\s*\d+\s*\)/i',
        '/BENCHMARK\s*\(/i',
        '/LOAD_FILE\s*\(/i',
        '/INTO\s+(OUTFILE|DUMPFILE)/i',
        '/INFORMATION_SCHEMA/i',
        '/CHAR\s*\(\s*\d+/i',
        '/0x[0-9a-f]{2,}/i',
        '/CAST\s*\(.*AS\s+(CHAR|INT)/i',
        '/CONVERT\s*\(/i',
        '/\bEXEC\b/i',
        '/xp_cmdshell/i',
        '/WAITFOR\s+DELAY/i',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return array_merge([
            'required' => 'Field :attribute wajib diisi.',
            'string' => 'Field :attribute harus berupa teks.',
            'email' => 'Field :attribute harus berupa email yang valid.',
            'min' => 'Field :attribute minimal :min karakter.',
            'max' => 'Field :attribute maksimal :max karakter.',
            'numeric' => 'Field :attribute harus berupa angka.',
            'date' => 'Field :attribute harus berupa tanggal yang valid.',
            'in' => 'Field :attribute harus salah satu dari: :values.',
            'unique' => 'Field :attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'mimes' => 'Field :attribute harus berupa file dengan tipe: :values.',
            'max.file' => 'Ukuran file :attribute maksimal :max kilobytes.',
            'security.xss' => 'Karakter yang Anda masukkan tidak diizinkan pada field :attribute.',
            'security.sqli' => 'Format input pada field :attribute terdeteksi sebagai pola yang tidak diizinkan.',
        ], $this->securityMessages());
    }

    protected function securityMessages(): array
    {
        return [];
    }

    protected function failedValidation(Validator $validator): void
    {
        if (config('security.audit.log_failed_attempts', true)) {
            \Log::warning('Validasi form gagal', [
                'errors' => $validator->errors()->toArray(),
                'ip' => $this->ip(),
                'url' => $this->fullUrl(),
                'user_id' => $this->user()?->id,
            ]);
        }

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        $sanitized = collect($this->all())
            ->map(function ($value) {
                if (is_string($value)) {
                    $value = str_replace("\0", '', $value);
                    return trim($value);
                }
                return $value;
            })
            ->all();

        $this->merge($sanitized);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->appendSecurityViolations($validator, $this->all());
        });
    }

    /**
     * Scan array input untuk pola XSS/SQLi (dipakai FormRequest dan controller).
     *
     * @return array<string, string> field => threat type (xss|sqli)
     */
    public static function scanForThreats(array $data, ?array $onlyKeys = null): array
    {
        $violations = [];
        $xssService = new XSSProtectionService();

        foreach (Arr::dot($data) as $key => $value) {
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            if ($onlyKeys !== null && ! in_array($key, $onlyKeys, true)) {
                continue;
            }

            if (static::matchesPatterns($value, static::$xssPatterns) || $xssService->detect($value)) {
                $violations[$key] = 'xss';
                continue;
            }

            if (static::matchesPatterns($value, static::$sqliPatterns)) {
                $violations[$key] = 'sqli';
            }
        }

        return $violations;
    }

    protected function appendSecurityViolations(Validator $validator, array $data): void
    {
        foreach (static::scanForThreats($data) as $field => $type) {
            $validator->errors()->add($field, $type === 'sqli'
                ? "Format input pada field {$field} terdeteksi sebagai pola yang tidak diizinkan."
                : "Karakter yang Anda masukkan tidak diizinkan pada field {$field}.");
        }
    }

    protected static function matchesPatterns(string $value, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tolak request jika ada input berbahaya (untuk controller tanpa FormRequest).
     */
    public static function assertSecureInput(array $data, ?array $onlyKeys = null): void
    {
        $violations = static::scanForThreats($data, $onlyKeys);

        if (empty($violations)) {
            return;
        }

        $firstType = array_values($violations)[0];
        $message = $firstType === 'sqli'
            ? 'Format input yang Anda masukkan terdeteksi sebagai pola yang tidak diizinkan.'
            : 'Karakter yang Anda masukkan tidak diizinkan pada field ini.';

        \Log::warning('Input berbahaya terdeteksi', [
            'violations' => $violations,
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $message,
                'errors' => collect($violations)->map(function ($type, $field) {
                    return $type === 'sqli'
                        ? "Format input pada field {$field} terdeteksi sebagai pola yang tidak diizinkan."
                        : "Karakter yang Anda masukkan tidak diizinkan pada field {$field}.";
                })->all(),
            ], 422)
        );
    }
}
