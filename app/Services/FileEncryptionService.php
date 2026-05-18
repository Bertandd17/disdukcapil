<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileEncryptionService
{
    private string $disk = 'secure';

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local') === 'secure' ? 'secure' : 'local';
    }

    public function encryptAndStoreFile($file, string $directory = ''): ?string
    {
        try {
            $fileContent = file_get_contents($file->getRealPath());
            $encryptedContent = Crypt::encrypt($fileContent);

            $filename = $this->generateEncryptedFilename($file->getClientOriginalName());
            $path = $directory ? trim($directory, '/').'/'.$filename : $filename;

            Storage::disk($this->disk)->put($path, $encryptedContent);

            return $this->encryptPath($path);
        } catch (\Exception $e) {
            Log::error('Failed to encrypt and store file', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function decryptAndRetrieveFile(string $encryptedPath): ?string
    {
        try {
            $decryptedPath = $this->decryptPath($encryptedPath);

            if (! Storage::disk($this->disk)->exists($decryptedPath)) {
                Log::warning('File not found', ['path' => $decryptedPath]);

                return null;
            }

            $encryptedContent = Storage::disk($this->disk)->get($decryptedPath);
            $decryptedContent = Crypt::decrypt($encryptedContent);

            return $decryptedContent;
        } catch (\Exception $e) {
            Log::error('Failed to decrypt and retrieve file', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function deleteFile(string $encryptedPath): bool
    {
        try {
            $decryptedPath = $this->decryptPath($encryptedPath);

            if (Storage::disk($this->disk)->exists($decryptedPath)) {
                Storage::disk($this->disk)->delete($decryptedPath);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete file', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function fileExists(string $encryptedPath): bool
    {
        try {
            $decryptedPath = $this->decryptPath($encryptedPath);

            return Storage::disk($this->disk)->exists($decryptedPath);
        } catch (\Exception $e) {
            Log::error('Failed to check file existence', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getFileSize(string $encryptedPath): ?int
    {
        try {
            $decryptedPath = $this->decryptPath($encryptedPath);

            if (Storage::disk($this->disk)->exists($decryptedPath)) {
                return Storage::disk($this->disk)->size($decryptedPath);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get file size', [
                'path' => $encryptedPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function generateEncryptedFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $randomString = Str::random(32);

        return $randomString.'.'.$extension;
    }

    public function encryptPath(string $path): string
    {
        return Crypt::encryptString($path);
    }

    public function decryptPath(string $encryptedPath): string
    {
        return Crypt::decryptString($encryptedPath);
    }
}
