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
 * - nama_lengkap: string untuk like query
 * - layanan_id: numeric untuk exact match
 */
class CariAntrianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'nama_lengkap' => 'nullable|string|max:100|regex:/^[\p{L}\s\.\-,]+$/u',
            'layanan_id' => 'nullable|numeric|exists:layanan,layanan_id',
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
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks',
            'nama_lengkap.regex' => 'Nama hanya boleh mengandung huruf, spasi, titik, koma, dan tanda hubung',
            'layanan_id.numeric' => 'ID layanan harus berupa angka',
            'layanan_id.exists' => 'Layanan tidak ditemukan',
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
        // Trim dan sanitize input
        if ($this->has('nomor_antrian')) {
            $this->merge([
                'nomor_antrian' => strtoupper(trim($this->nomor_antrian)),
            ]);
        }

        if ($this->has('nama_lengkap')) {
            $this->merge([
                'nama_lengkap' => trim(strip_tags($this->nama_lengkap)),
            ]);
        }
    }
}
