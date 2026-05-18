<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KtpUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'antrian_online_id' => [
                'required',
                'string',
                'uuid',
                'exists:antrian_online,antrian_online_id',
            ],
            'ktp_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'antrian_online_id.required' => 'antrian_online_id wajib diisi.',
            'antrian_online_id.uuid' => 'antrian_online_id harus berformat UUID.',
            'antrian_online_id.exists' => 'Antrian tidak ditemukan.',
            'ktp_image.required' => 'File KTP wajib diunggah pada field ktp_image.',
            'ktp_image.image' => 'File KTP harus berupa gambar.',
            'ktp_image.mimes' => 'Format gambar KTP harus jpg, jpeg, atau png.',
            'ktp_image.max' => 'Ukuran gambar KTP maksimal 5 MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->antrian_online_id)) {
            $this->merge([
                'antrian_online_id' => trim($this->antrian_online_id),
            ]);
        }
    }
}
