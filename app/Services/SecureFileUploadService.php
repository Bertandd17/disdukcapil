<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service untuk handle file upload dengan encryption
 */
class SecureFileUploadService
{
    /**
     * Upload file dengan security (encrypt path dan metadata)
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $folder  Folder tujuan (contoh: antrian/ktp)
     * @param  string  $originalName  Nama asli file (opsional)
     * @return array [
     *               'secure_path' => string (encrypted),
     *               'unique_id' => string (encrypted),
     *               'original_filename' => string (encrypted),
     *               'mime_type' => string,
     *               'size' => int
     *               ]
     */
    public function uploadSecureFile($file, $folder, $originalName = null): array
    {
        $disk = 'secure';

        // Generate unique ID untuk dokumen
        $uniqueId = Str::uuid().'_'.time();
        $extension = $file->getClientOriginalExtension();

        // Buat filename yang aman (bukan nama asli user)
        $secureFilename = $uniqueId.'.'.$extension;

        // Simpan file ke disk
        $filePath = $file->storeAs($folder, $secureFilename, $disk);

        // Get file info
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Encrypt path dan metadata
        $encryptedPath = $this->encrypt($filePath);
        $encryptedUniqueId = $this->encrypt($uniqueId);
        $encryptedOriginalName = $this->encrypt($originalName ?? $file->getClientOriginalName());

        return [
            'secure_path' => $encryptedPath,
            'unique_id' => $encryptedUniqueId,
            'original_filename' => $encryptedOriginalName,
            'mime_type' => $mimeType,
            'size' => $size,
        ];
    }

    /**
     * Encrypt string
     */
    public function encrypt(string $value): string
    {
        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt file metadata', [
                'value' => Str::limit($value, 100),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt string
     */
    public function decrypt(string $encryptedValue): string
    {
        try {
            return Crypt::decryptString($encryptedValue);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt file metadata', [
                'encrypted_value' => Str::limit($encryptedValue, 100),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete file secara aman
     */
    public function deleteSecureFile(string $encryptedPath): bool
    {
        try {
            $disk = 'secure';
            $path = $this->decrypt($encryptedPath);

            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to delete secure file', [
                'encrypted_path' => Str::limit($encryptedPath, 100),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate file upload
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateFile($file, array $allowedMimeTypes = [], int $maxSizeInMB = 10): array
    {
        $errors = [];

        // Check file size
        $maxSizeInBytes = $maxSizeInMB * 1024 * 1024;
        if ($file->getSize() > $maxSizeInBytes) {
            $errors[] = "Ukuran file maksimal {$maxSizeInMB} MB.";
        }

        // Check MIME type
        if (! empty($allowedMimeTypes)) {
            if (! in_array($file->getMimeType(), $allowedMimeTypes)) {
                $errors[] = 'Tipe file tidak diizinkan.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
