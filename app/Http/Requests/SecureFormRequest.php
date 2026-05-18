<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class SecureFormRequest extends FormRequest
{
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
    public function rules(): array
    {
        return [
            // Rules should be defined in child classes
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
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
            'date' => 'Field :attribute harus berupa tanggal yang valid.',
            'in' => 'Field :attribute harus salah satu dari: :values.',
            'unique' => 'Field :attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'mimes' => 'Field :attribute harus berupa file dengan tipe: :values.',
            'max.file' => 'Ukuran file :attribute maksimal :max kilobytes.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log validation failures for security monitoring
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

    /**
     * Prepare inputs for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim all string inputs
        $this->merge(
            collect($this->all())
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->all()
        );
    }
}
