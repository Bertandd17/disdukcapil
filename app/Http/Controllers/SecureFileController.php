<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Secure File Controller
 *
 * Controller untuk serve file yang tersimpan di private/secure folder
 * File hanya bisa diakses oleh user yang berhak (Admin atau pemilik file)
 */
class SecureFileController extends Controller
{
    /**
     * Serve uploaded file dengan authentication check
     *
     * @param  string  $path  Path file di storage (contoh: antrian/ktp/filename.jpg)
     * @return \Illuminate\Http\Response
     */
    public function serve($path)
    {
        // Check authentication
        if (!Auth::check()) {
            abort(401, 'Anda harus login terlebih dahulu');
        }

        // Validate path untuk security
        if (!$this->isValidPath($path)) {
            abort(403, 'Akses ditolak');
        }

        // Get file dari secure storage
        $disk = 'secure';
        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        // Check authorization (Admin atau owner file)
        if (!$this->isAuthorized($path)) {
            abort(403, 'Anda tidak memiliki akses ke file ini');
        }

        // Get file info
        $mimeType = Storage::disk($disk)->mimeType($path);
        $fileName = basename($path);

        // Stream file to browser
        return response()->streamDownload(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            fpassthru($stream);
        }, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Check if path is valid (prevent directory traversal)
     *
     * @param  string  $path
     * @return bool
     */
    private function isValidPath($path): bool
    {
        // Hanya izinkan path di bawah folder tertentu
        $allowedFolders = [
            'antrian/ktp',
            'antrian/selfie',
            'akte_lahir',
            'akte_kematian',
            'lahir_mati',
            'keagamaan',
        ];

        // Normalize path
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // Check jika path mengandung '..' atau komponen berbahaya
        if (str_contains($path, '..') || str_starts_with($path, '.')) {
            return false;
        }

        // Check jika path diizinkan
        foreach ($allowedFolders as $folder) {
            if (str_starts_with($path, $folder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user berhak mengakses file ini
     *
     * @param  string  $path
     * @return bool
     */
    private function isAuthorized($path): bool
    {
        $user = Auth::user();

        // Admin selalu punya akses
        if ($user && $user->hasRole('Admin')) {
            return true;
        }

        // User biasa hanya akses file miliknya sendiri
        // Implementasi logic di sini sesuai kebutuhan

        return true; // Sementara return true
    }

    /**
     * Get file info
     *
     * @param  string  $path
     * @return array
     */
    public function fileInfo($path)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$this->isValidPath($path)) {
            return response()->json(['error' => 'Invalid path'], 403);
        }

        $disk = 'secure';
        if (!Storage::disk($disk)->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if (!$this->isAuthorized($path)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json([
            'path' => $path,
            'filename' => basename($path),
            'size' => Storage::disk($disk)->size($path),
            'last_modified' => Storage::disk($disk)->lastModified($path),
            'mime_type' => Storage::disk($disk)->mimeType($path),
        ]);
    }
}
