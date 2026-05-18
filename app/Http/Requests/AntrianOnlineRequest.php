<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Form Request untuk validasi Antrian Online
 *
 * Strict Data Type Validation:
 * - layanan_id: numeric dan exists di database
 * - nama_lengkap: string dengan max length
 * - alamat: nullable string
 * - tanggal_lahir: nullable date format
 */
class AntrianOnlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Public access, tidak perlu auth
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
            'layanan_id' => 'required|numeric|exists:layanan,layanan_id',
            'nama_lengkap' => 'required|string|max:100|regex:/^[\p{L}\s\.\-,]+$/u',
            'alamat' => 'nullable|string|max:500',
            'tanggal_lahir' => 'nullable|date|date_format:Y-m-d|before:today',
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
            'layanan_id.required' => 'Layanan harus dipilih',
            'layanan_id.numeric' => 'ID layanan harus berupa angka',
            'layanan_id.exists' => 'Layanan tidak ditemukan',
            'nama_lengkap.required' => 'Nama lengkap harus diisi',
            'nama_lengkap.string' => 'Nama lengkap harus berupa teks',
            'nama_lengkap.max' => 'Nama lengkap maksimal 100 karakter',
            'nama_lengkap.regex' => 'Nama lengkap hanya boleh mengandung huruf, spasi, titik, koma, dan tanda hubung',
            'alamat.string' => 'Alamat harus berupa teks',
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'tanggal_lahir.date_format' => 'Format tanggal lahir harus YYYY-MM-DD',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini',
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
     * Sanitasi input otomatis
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim semua string input
        $this->merge([
            'nama_lengkap' => is_string($this->nama_lengkap) ? trim($this->nama_lengkap) : $this->nama_lengkap,
            'alamat' => is_string($this->alamat) ? trim($this->alamat) : $this->alamat,
        ]);

        // Strip tags dari input string
        if (is_string($this->nama_lengkap)) {
            $this->merge([
                'nama_lengkap' => strip_tags($this->nama_lengkap),
            ]);
        }

        if (is_string($this->alamat)) {
            $this->merge([
                'alamat' => strip_tags($this->alamat),
            ]);
        }
    }
}
