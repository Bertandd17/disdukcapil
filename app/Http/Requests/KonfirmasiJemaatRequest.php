<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KonfirmasiJemaatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:diterima,ditolak',
            'catatan' => 'nullable|string|max:500',
            'tanggal_perkawinan' => 'nullable|required_if:status,diterima|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status harus diterima atau ditolak.',
            'catatan.max' => 'Catatan tidak boleh lebih dari 500 karakter.',
            'tanggal_perkawinan.required_if' => 'Tanggal perkawinan wajib diisi jika status diterima.',
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
