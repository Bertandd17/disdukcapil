<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TentukanTanggalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_perkawinan' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_perkawinan.required' => 'Tanggal perkawinan wajib diisi.',
            'tanggal_perkawinan.date' => 'Format tanggal tidak valid.',
            'tanggal_perkawinan.after' => 'Tanggal perkawinan harus setelah hari ini.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
