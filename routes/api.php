<?php

use App\Http\Controllers\Api\EasyOcrController;
use App\Http\Controllers\Api\KtpOcrController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OcrController;
use App\Http\Controllers\Api\PernikahanController;
use App\Http\Controllers\AntrianOnlineController;
use App\Http\Controllers\StatistikPublikController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// EasyOCR KTP API Routes
Route::prefix('ocr')->group(function () {
    // Upload dan proses gambar KTP (EasyOcrController)
    Route::post('/upload', [EasyOcrController::class, 'upload']);

    // Proses multiple images
    Route::post('/batch', [EasyOcrController::class, 'batchUpload']);

    // Cek status hasil OCR
    Route::get('/status/{antrianId}', [EasyOcrController::class, 'status']);

    // Ambil hasil OCR
    Route::get('/result/{antrianId}', [EasyOcrController::class, 'result']);

    // Proses OCR (integrated dengan Antrian_Online)
    Route::post('/process', [AntrianOnlineController::class, 'Proses_Ocr_Easy']);

    // Proses OCR dengan Google Vision (fallback)
    Route::post('/process-vision', [AntrianOnlineController::class, 'Proses_Ocr_Vision']);

    // Diagnostic endpoint
    Route::get('/diagnose', [AntrianOnlineController::class, 'Diagnose_Ocr']);

    /*
    |--------------------------------------------------------------------------
    | ADVANCED OCR API ROUTES (New Implementation)
    |--------------------------------------------------------------------------
    |
    | Routes untuk OcrController dengan fitur enhanced:
    | - Better field extraction
    | - NIK checksum validation
    | - Advanced parsing service
    | - Queue-based processing
    |
    */

    // Health check
    Route::get('/health', [OcrController::class, 'health']);

    // Upload dengan opsi async/sync
    Route::post('/v2/upload', [OcrController::class, 'upload']);

    // Check status
    Route::get('/v2/status/{id}', [OcrController::class, 'status']);

    // Get result
    Route::get('/v2/result/{id}', [OcrController::class, 'result']);

    // Batch processing
    Route::post('/v2/batch', [OcrController::class, 'batch']);

    // Batch status
    Route::get('/v2/batch/{batchId}', [OcrController::class, 'batchStatus']);

    // Reprocess OCR
    Route::post('/v2/reprocess/{id}', [OcrController::class, 'reprocess']);

    // Test endpoint
    Route::post('/v2/test', [OcrController::class, 'test']);

    // KTP OCR extraction pipeline (new dedicated endpoint)
    Route::post('/ktp/extract', [KtpOcrController::class, 'extract']);
});

/*
|--------------------------------------------------------------------------
| PUBLIK STATISTIK API ROUTES
|--------------------------------------------------------------------------
|
| API routes untuk statistik publik (tanpa autentikasi)
| Digunakan untuk visualisasi data di halaman publik
|
*/

Route::prefix('statistik')->name('api.statistik.')->group(function () {
    // Statistik Penduduk
    Route::get('/penduduk', [StatistikPublikController::class, 'penduduk'])->name('penduduk');
    Route::get('/penduduk/tren', [StatistikPublikController::class, 'pendudukTrend'])->name('penduduk.tren');
    
    // Statistik Dokumen
    Route::get('/dokumen', [StatistikPublikController::class, 'dokumen'])->name('dokumen');
    Route::get('/dokumen/ringkasan', [StatistikPublikController::class, 'dokumenRingkasan'])->name('dokumen.ringkasan');
    
    // Statistik Layanan
    Route::get('/layanan', [StatistikPublikController::class, 'layanan'])->name('layanan');
    Route::get('/layanan/ringkasan', [StatistikPublikController::class, 'layananRingkasan'])->name('layanan.ringkasan');
    Route::get('/layanan/tren', [StatistikPublikController::class, 'layananTren'])->name('layanan.tren');
    
    // Combo data
    Route::get('/combo', [StatistikPublikController::class, 'combo'])->name('combo');
    
    // Referensi
    Route::get('/kecamatan', [StatistikPublikController::class, 'kecamatan'])->name('kecamatan');
    Route::get('/tahun', [StatistikPublikController::class, 'tahunTersedia'])->name('tahun');
});

/*
|--------------------------------------------------------------------------
| ADMIN NOTIFICATION API ROUTES
|--------------------------------------------------------------------------
|
| API routes untuk notifikasi real-time admin
| Dipanggil via polling dari frontend admin
|
*/

Route::middleware(['auth'])->prefix('notifications')->name('api.notifications.')->group(function () {
    // Cek notifikasi baru (polling)
    Route::get('/check', [NotificationController::class, 'checkNew'])->name('check');

    // Tandai notifikasi sebagai dibaca
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');

    // Get semua notifikasi unread
    Route::get('/unread', [NotificationController::class, 'getUnread'])->name('unread');
});

/*
|--------------------------------------------------------------------------
| ANTRIAN ONLINE API ROUTES
|--------------------------------------------------------------------------
|
| API routes untuk antrian online
| Digunakan untuk autofill data di layanan mandiri
|
*/

Route::prefix('antrian')->name('api.antrian.')->group(function () {   
    // Cek daily limit - apakah user hari ini sudah mengajukan layanan yang sama
    Route::get('/check-daily-limit', [AntrianOnlineController::class, 'checkDailyLimit'])->name('check_daily_limit');

    // Get data antrian by nomor antrian (dengan validasi layanan)
    Route::get('/{nomorAntrian}', [AntrianOnlineController::class, 'getApiData'])->name('get');
});

/*
|--------------------------------------------------------------------------
| PERNIKAHAN API ROUTES
|--------------------------------------------------------------------------
|
| API routes untuk layanan pencatatan perkawinan
| Digunakan untuk form pernikahan dan tracking status
|
*/

Route::prefix('pernikahan')->name('api.pernikahan.')->group(function () {
    // Public endpoints (tanpa auth)
    Route::get('/antrian', [PernikahanController::class, 'ambilNomorAntrian'])->name('antrian');
    Route::get('/status/{pernikahan_id}', [PernikahanController::class, 'status'])->name('status');
    Route::get('/antrian/{nomor_antrian}', [PernikahanController::class, 'statusByNomorAntrian'])->name('status.by_nomor');
    Route::get('/jenis-agama', [PernikahanController::class, 'listJenisAgama'])->name('jenis-agama');
    Route::get('/keagamaan', [PernikahanController::class, 'listKeagamaan'])->name('keagamaan');

    // Authenticated endpoints
    Route::middleware(['auth'])->group(function () {
        Route::post('/submit', [PernikahanController::class, 'submit'])->name('submit');
        Route::post('/{pernikahan_id}/upload-dokumen', [PernikahanController::class, 'uploadDokumen'])->name('upload');
    });

    // Admin endpoints (auth + admin role)
    Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
        Route::get('/calendar', [PernikahanController::class, 'calendarData'])->name('calendar');
        Route::get('/detail/{id}', [PernikahanController::class, 'detail'])->name('detail');
        Route::post('/{id}/approve', [PernikahanController::class, 'approveTanggal'])->name('approve');
        Route::post('/{id}/reject', [PernikahanController::class, 'rejectTanggal'])->name('reject');
        Route::post('/{id}/reject-doc', [PernikahanController::class, 'rejectDokumen'])->name('reject-doc');
        Route::post('/{id}/verify-all', [PernikahanController::class, 'verifyAll'])->name('verify-all');
        Route::post('/{id}/upload-berkas', [PernikahanController::class, 'uploadBerkas'])->name('upload-berkas');
    });
});
