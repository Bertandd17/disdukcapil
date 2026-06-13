<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request untuk validasi Pencarian Antrian
 *
 * Security Features:
 * - nomor_antrian: string dengan format valid
 * - nik: 16 digit untuk pencarian lacak berkas
 * - layanan_id: string untuk exact match
 */
class CariAntrianRequest extends FormRequest
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
            'nomor_antrian' => 'nullable|string|max:20|regex:/^[A-Z0-9\-]+$/',
            'nik' => 'nullable|string|regex:/^\d{16}$/',
            'layanan_id' => 'nullable|string|exists:layanan,layanan_id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_antrian.string' => 'Nomor antrian harus berupa teks',
            'nomor_antrian.regex' => 'Format nomor antrian tidak valid',
            'nik.string' => 'NIK harus berupa angka',
            'nik.regex' => 'NIK harus 16 digit angka',
            'layanan_id.string' => 'ID layanan harus berupa teks',
            'layanan_id.exists' => 'Layanan tidak ditemukan',
        ];
    }

    /**
     * Handle a failed validation attempt.
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
        if ($this->has('nomor_antrian')) {
            $this->merge([
                'nomor_antrian' => strtoupper(trim($this->nomor_antrian)),
            ]);
        }

        if ($this->has('nik')) {
            $this->merge([
                'nik' => preg_replace('/\D/', '', trim($this->nik)),
            ]);
        }
    }
}
