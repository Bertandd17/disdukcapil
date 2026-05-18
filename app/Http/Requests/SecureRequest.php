<?php

namespace App\Http\Requests;

use App\Services\SQLInjectionProtectionService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class SecureRequest extends FormRequest
{
    /**
     * SQL injection protection service
     *
     * @var \App\Services\SQLInjectionProtectionService
     */
    protected $sqlProtection;

    /**
     * Create a new request instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->sqlProtection = new SQLInjectionProtectionService;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'Field :attribute wajib diisi.',
            'string' => 'Field :attribute harus berupa teks.',
            'email' => 'Field :attribute harus berupa email yang valid.',
            'min' => 'Field :attribute minimal :min karakter.',
            'max' => 'Field :attribute maksimal :max karakter.',
            'numeric' => 'Field :attribute harus berupa angka.',
            'integer' => 'Field :attribute harus berupa bilangan bulat.',
            'date' => 'Field :attribute harus berupa tanggal yang valid.',
            'in' => 'Field :attribute harus salah satu dari: :values.',
            'unique' => 'Field :attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'mimes' => 'Field :attribute harus berupa file dengan tipe: :values.',
            'max.file' => 'Ukuran file :attribute maksimal :max kilobytes.',
            'digits' => 'Field :attribute harus :digits digit.',
            'digits_between' => 'Field :attribute harus antara :min dan :max digit.',
            'alpha' => 'Field :attribute hanya boleh berisi huruf.',
            'alpha_num' => 'Field :attribute hanya boleh berisi huruf dan angka.',
            'alpha_dash' => 'Field :attribute hanya boleh berisi huruf, angka, dan dash.',
            'url' => 'Field :attribute harus berupa URL yang valid.',
            'ip' => 'Field :attribute harus berupa IP address yang valid.',
            'regex' => 'Format :attribute tidak valid.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Prepare inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim semua string inputs
        $inputs = $this->all();

        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $inputs[$key] = trim($value);
            }
        }

        $this->merge($inputs);

        // Check untuk SQL injection
        $this->checkForSQLInjection($inputs);
    }

    /**
     * Check for SQL injection patterns
     *
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function checkForSQLInjection(array $inputs): void
    {
        if ($this->sqlProtection->detectInArray($inputs, $this->route()->getName())) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Input mengandung karakter yang tidak diizinkan.',
                ], 400)
            );
        }
    }

    /**
     * Get validated and sanitized data
     */
    public function validatedSafe(): array
    {
        $validated = $this->validated();

        // Sanitize semua string values
        return $this->sqlProtection->sanitizeArray($validated);
    }

    /**
     * Get sanitized input
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        \Log::warning('Security Event: '.$event, array_merge($context, [
            'ip' => $this->ip(),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'user_agent' => $this->userAgent(),
            'user_id' => $this->user()?->id,
        ]));
    }

    /**
     * Get sanitized input
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function sanitized($key = null, $default = null)
    {
        if ($key === null) {
            return $this->sqlProtection->sanitizeArray($this->all());
        }

        $value = $this->input($key, $default);

        if (is_string($value)) {
            return $this->sqlProtection->sanitize($value);
        }

        if (is_array($value)) {
            return $this->sqlProtection->sanitizeArray($value);
        }

        return $value;
    }

    /**
     * Validate NIK format
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     */
    public function validateNik($attribute, $value, $parameters): bool
    {
        return preg_match('/^[0-9]{16}$/', $value) === 1;
    }

    /**
     * Validate phone number
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     */
    public function validatePhone($attribute, $value, $parameters): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $value) === 1;
    }

    /**
     * Validate safe string (no special characters)
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     */
    public function validateSafeString($attribute, $value, $parameters): bool
    {
        return preg_match('/^[a-zA-Z0-9\s\-_\.]+$/', $value) === 1;
    }

    /**
     * Validate safe URL (prevent javascript:, etc)
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  array  $parameters
     */
    public function validateSafeUrl($attribute, $value, $parameters): bool
    {
        return ! preg_match('/^(javascript|vbscript|data):/i', $value);
    }
}
