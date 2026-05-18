<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;
use PDOException;

/**
 * DatabaseException Class
 *
 * Custom exception handler untuk database errors
 * Menampilkan pesan error yang user-friendly dan informatif
 */
class DatabaseException extends Exception
{
    /**
     * Format exception untuk ditampilkan ke user
     *
     * @param \Exception $e
     * @return array
     */
    public static function formatForUser(Exception $e): array
    {
        // Default error info
        $errorInfo = [
            'user_message' => 'Maaf, sistem mengalami kesalahan saat memproses permintaan Anda.',
            'technical_detail' => $e->getMessage(),
            'location' => self::getErrorLocation($e),
            'solution' => 'Silakan coba lagi. Jika masalah berlanjut, hubungi administrator.',
            'error_code' => 'DB_ERROR_' . strtoupper(substr(md5(uniqid()), 0, 6)),
        ];

        // Analyze error type dan berikan pesan spesifik
        $errorMessage = $e->getMessage();

        // 1. SQL State Error (tipe data tidak cocok)
        if (strpos($errorMessage, 'SQLSTATE[22007]') !== false ||
            strpos($errorMessage, 'Incorrect integer value') !== false ||
            strpos($errorMessage, 'Invalid datetime format') !== false) {

            $errorInfo['user_message'] = 'Terjadi kesalahan tipe data dalam penyimpanan informasi.';
            $errorInfo['technical_detail'] = 'Type mismatch: kolom database tidak sesuai dengan data yang dikirim.';
            $errorInfo['solution'] = 'Sistem sedang dalam perbaikan. Silakan coba beberapa saat lagi atau hubungi administrator jika masalah berlanjut.';
            $errorInfo['error_code'] = 'DB_TYPE_MISMATCH_001';
        }

        // 2. Duplicate Entry (unique constraint violation)
        elseif (strpos($errorMessage, 'Duplicate entry') !== false ||
            strpos($errorMessage, 'UNIQUE constraint failed') !== false ||
            $e->getCode() === 23000) {

            $errorInfo['user_message'] = 'Data yang Anda masukkan sudah ada dalam sistem.';
            $errorInfo['technical_detail'] = 'Duplicate entry: Data unik sudah terdaftar.';
            $errorInfo['solution'] = 'Gunakan data yang berbeda. Periksa kembali informasi yang Anda masukkan.';
            $errorInfo['error_code'] = 'DB_DUPLICATE_001';
        }

        // 3. Foreign Key Constraint
        elseif (strpos($errorMessage, 'foreign key constraint') !== false ||
            strpos($errorMessage, 'FOREIGN KEY constraint failed') !== false) {

            $errorInfo['user_message'] = 'Tidak dapat menyimpan data karena terdata keterkaitan dengan data lain.';
            $errorInfo['technical_detail'] = 'Foreign key constraint: Data referensi tidak ditemukan.';
            $errorInfo['solution'] = 'Pastikan semua data referensi sudah terdaftar dengan benar dalam sistem.';
            $errorInfo['error_code'] = 'DB_FOREIGN_KEY_001';
        }

        // 4. Connection Error
        elseif ($e instanceof PDOException ||
            strpos($errorMessage, 'Connection') !== false) {

            $errorInfo['user_message'] = 'Tidak dapat terhubung ke database.';
            $errorInfo['technical_detail'] = 'Database connection failed.';
            $errorInfo['solution'] = 'Periksa koneksi database atau hubungi administrator segera.';
            $errorInfo['error_code'] = 'DB_CONNECTION_001';
        }

        // 5. Table Not Found
        elseif (strpos($errorMessage, "doesn't exist") !== false ||
            strpos($errorMessage, 'Base table or view not found') !== false) {

            $errorInfo['user_message'] = 'Tabel data yang diperlukan tidak ditemukan.';
            $errorInfo['technical_detail'] = 'Table not found: Tabel database belum dibuat atau salah nama.';
            $errorInfo['solution'] = 'Jalankan migration database atau hubungi administrator.';
            $errorInfo['error_code'] = 'DB_TABLE_NOT_FOUND_001';
        }

        // 6. Null Constraint Violation
        elseif (strpos($errorMessage, 'cannot be null') !== false) {

            $errorInfo['user_message'] = 'Ada data wajib yang belum terisi.';
            $errorInfo['technical_detail'] = 'Null constraint violation: Field required tidak diisi.';
            $errorInfo['solution'] = 'Lengkapi semua form yang wajib diisi (biasanya ditandai dengan asterisk *).';
            $errorInfo['error_code'] = 'DB_NULL_CONSTRAINT_001';
        }

        // Log technical error untuk debugging
        Log::error('Database Error', [
            'error_code' => $errorInfo['error_code'],
            'message' => $errorMessage,
            'trace' => $e->getTraceAsString(),
            'location' => $errorInfo['location'],
        ]);

        return $errorInfo;
    }

    /**
     * Extract error location dari exception
     *
     * @param \Exception $e
     * @return string
     */
    private static function getErrorLocation(Exception $e): string
    {
        $trace = $e->getTrace();

        // Cari frame pertama yang bukan dari file ini
        foreach ($trace as $frame) {
            if (isset($frame['file']) &&
                strpos($frame['file'], 'app/Http/Controllers') !== false &&
                strpos($frame['file'], 'vendor') === false) {

                $file = str_replace(base_path(), '', $frame['file']);
                $line = $frame['line'] ?? '?';
                return "{$file}:{$line}";
            }
        }

        // Fallback ke exception file dan line
        $file = str_replace(base_path(), '', $e->getFile());
        $line = $e->getLine();
        return "{$file}:{$line}";
    }

    /**
     * Render error sebagai array untuk JSON response
     *
     * @param array $errorInfo
     * @return array
     */
    public static function toJsonResponse(array $errorInfo): array
    {
        return [
            'success' => false,
            'message' => $errorInfo['user_message'],
            'error_code' => $errorInfo['error_code'],
            'location' => $errorInfo['location'],
            'solution' => $errorInfo['solution'],
        ];
    }
}
