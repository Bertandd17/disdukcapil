<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_dokumen' => 'required|string|in:surat_keterangan,ktp_mempelai_pria,ktp_mempelai_wanita,kk_mempelai_pria,kk_mempelai_wanita,surat_ijin_orang_tua,surat_n1_n2_n4,foto_prewedding,bukti_pembayaran,lainnya',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_dokumen.required' => 'Jenis dokumen wajib dipilih.',
            'jenis_dokumen.in' => 'Jenis dokumen tidak valid.',
            'file.required' => 'File wajib diupload.',
            'file.file' => 'File harus berupa file yang valid.',
            'file.mimes' => 'File harus berupa PDF, JPG, JPEG, atau PNG.',
            'file.max' => 'Ukuran file tidak boleh lebih dari 5MB.',
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
