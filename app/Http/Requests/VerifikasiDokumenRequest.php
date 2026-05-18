<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifikasiDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dokumen_id' => 'required|array',
            'dokumen_id.*' => 'exists:dokumen_pernikahan,id',
            'status' => 'required|array',
            'status.*' => 'in:DIVERIFIKASI,DITOLAK',
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'dokumen_id.required' => 'Dokumen wajib dipilih.',
            'dokumen_id.array' => 'Format dokumen tidak valid.',
            'dokumen_id.*.exists' => 'Dokumen tidak ditemukan.',
            'status.required' => 'Status wajib diisi.',
            'status.array' => 'Format status tidak valid.',
            'status.*.in' => 'Status harus DIVERIFIKASI atau DITOLAK.',
            'cataton.array' => 'Format catatan tidak valid.',
            'catatan.*.max' => 'Catatan tidak boleh lebih dari 500 karakter.',
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
