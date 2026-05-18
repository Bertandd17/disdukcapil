<?php

namespace App\Http\Requests;

class SecureFileUploadRequest extends SecureFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSize = config('security.file_upload.max_size', 5242880); // 5MB in KB
        $maxSizeKB = $maxSize / 1024;

        $allowedMimes = implode(',', config('security.file_upload.allowed_mime_types', [
            'image/jpeg',
            'image/jpg',
            'image/png',
        ]));

        return [
            'file' => [
                'required',
                'file',
                'mimes:' . $allowedMimes,
                'max:' . $maxSizeKB,
                // Additional MIME validation
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $mimeType = $value->getMimeType();
                        $allowedMimes = config('security.file_upload.allowed_mime_types', []);

                        if (!in_array($mimeType, $allowedMimes)) {
                            $fail('Tipe file tidak diizinkan. Tipe terdeteksi: ' . $mimeType);
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $maxSizeKB = config('security.file_upload.max_size', 5242880) / 1024;

        return [
            'file.required' => 'File wajib diunggah',
            'file.file' => 'File harus berupa file yang valid',
            'file.mimes' => 'Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG.',
            'file.max' => "Ukuran file maksimal {$maxSizeKB} KB",
        ];
    }
}
