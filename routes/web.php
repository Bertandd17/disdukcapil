<?php

use App\Http\Controllers\Admin\Admin_Controller;
use App\Http\Controllers\Admin\Berita_Controller;
use App\Http\Controllers\Admin\OrganisasiController as AdminOrganisasiController;
use App\Http\Controllers\Admin\PernikahanController as AdminPernikahanController;
use App\Http\Controllers\Keagamaan\PernikahanController as KeagamaanPernikahanController;
use App\Http\Controllers\User\OrganisasiController as UserOrganisasiController;
use App\Http\Controllers\User\PernikahanController as UserPernikahanController;
use App\Http\Controllers\AkteKematianController;
use App\Http\Controllers\AkteLahirController;
use App\Http\Controllers\Antrian_Online_Controller;
use App\Http\Controllers\Auth\Login_Controller;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\KartKeluargaController;
use App\Http\Controllers\LahirMatiController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Pengguna_Controller;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\SecureFileController;
use App\Http\Controllers\StatistikPublikController;
use App\Http\Controllers\DasarHukumController;
use App\Http\Controllers\PenghargaanController;
use App\Models\Layanan_Model;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Halaman yang bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/

// Home page - Beranda
Route::get('/', [PageController::class, 'index'])->name('home');


// API Routes untuk layanan (public)
Route::get('/api/layanan', function () {
    $data_layanan = Layanan_Model::all();

    return response()->json([
        'success' => true,
        'data' => $data_layanan,
    ]);
})->name('api.layanan');

// API Routes untuk tempat ibadah (public) - digunakan untuk form layanan mandiri
Route::get('/api/tempat-ibadah/{agama}', function ($agama) {
    try {
        $namaAgama = urldecode($agama);

        $jenis = \Illuminate\Support\Facades\DB::table('jenis_keagamaan')
            ->where('nama_jenis_keagamaan', $namaAgama)
            ->first();

        if (!$jenis) return response()->json([]);

        $data = \Illuminate\Support\Facades\DB::table('users')
            ->join('keagamaan', 'users.id', '=', 'keagamaan.user_id')
            ->where('keagamaan.jenis_keagamaan_id', $jenis->jenis_keagamaan_id)
            ->where('keagamaan.status', 'aktif')
            ->select(
                'keagamaan.keagamaan_id',
                'keagamaan.nama_tempat_ibadah as nama_tempat_ibadah'
            )
            ->get();

        return response()->json($data);
    } catch (\Exception $e) {
        return response()->json([
            'pesan_error' => $e->getMessage(),
            'info' => 'Pastikan kolom nama_tempat_ibadah sudah ada di tabel keagamaan'
        ], 500);
    }
})->name('api.tempat-ibadah');

// Rute untuk menangkap data form layanan mandiri
Route::post('/layanan-mandiri/akte-kematian', [AkteKematianController::class, 'store'])->name('aktekematian.store');
Route::post('/layanan-mandiri/lahir-mati', [LahirMatiController::class, 'store'])->name('lahirmati.store');
Route::get('/unduh-formulir', [PageController::class, 'unduhFormulir'])->name('unduh-formulir');

// Antrian Online (Public)
Route::prefix('antrian-online')->group(function () {
    Route::get('/', [Antrian_Online_Controller::class, 'Tampil_Antrian'])->name('antrian-online');
    Route::post('/', [Antrian_Online_Controller::class, 'Tambah_Antrian'])->name('antrian.store');
    Route::get('/cari', [Antrian_Online_Controller::class, 'Cari_Antrian'])->name('antrian.search');
    Route::post('/cari', [Antrian_Online_Controller::class, 'Cari_Antrian_Post'])->name('antrian-online.cari');
    Route::get('/detail/{nomor_antrian}', [Antrian_Online_Controller::class, 'Get_Detail_Antrian'])->name('antrian-online.detail');
    Route::get('/statistik', [Antrian_Online_Controller::class, 'Get_Statistik_Antrian'])->name('antrian.statistik');
    Route::get('/lacak', [Antrian_Online_Controller::class, 'Lacak_Berkas'])->name('antrian.lacak');
    Route::post('/lacak', [Antrian_Online_Controller::class, 'Lacak_Berkas_Post'])->name('antrian-online.lacak');
    Route::post('/lacak-berkas', [Antrian_Online_Controller::class, 'Lacak_Berkas_Post'])->name('antrian-online.lacak-berkas');
    Route::get('/lacak-berkas/download/{id}', [Antrian_Online_Controller::class, 'Download_Berkas_Final'])->name('lacak-berkas.download-final');
    Route::get('/get-data/{nomor_antrian}', [Antrian_Online_Controller::class, 'Get_Data_Antrian'])->name('antrian.get-data');
    
    // Test endpoint for debugging
    Route::get('/test', [Antrian_Online_Controller::class, 'Test_Search'])->name('antrian.test');

    // Auto-OCR multi-step flow
    Route::post('/draft', [Antrian_Online_Controller::class, 'Buat_Draft_Antrian'])
        ->middleware('throttle:10,1')
        ->name('antrian-online.draft');
    Route::get('/draft/{antrian_online_id}/ocr-status', [Antrian_Online_Controller::class, 'Get_Ocr_Status_Draft'])
        ->whereUuid('antrian_online_id')
        ->name('antrian-online.draft.ocr-status');
    Route::post('/finalize/{antrian_online_id}', [Antrian_Online_Controller::class, 'Finalisasi_Antrian'])
        ->whereUuid('antrian_online_id')
        ->name('antrian-online.finalize');

    // Serve dokumen final pernikahan dengan URL bersih (public, lookup by pernikahan_id)
    Route::get('/dokumen-final/{pernikahanId}/{jenis}', [Antrian_Online_Controller::class, 'viewDokumenFinal'])
        ->where('jenis', 'akta_pernikahan|kk_pasangan|kk_ortu_pria|kk_ortu_wanita')
        ->name('antrian.dokumen-final');
});

// Layanan Pernikahan dari layanan mandiri modal (tanpa login)
// ROUTE INI HARUS DIDEFINISIKAN SEBELUM route generik layanan-mandiri/{jenis_layanan}
// agar tidak ter-capture oleh route generik tersebut
Route::post('/layanan-mandiri/perkawinan', [UserPernikahanController::class, 'storeFromLayananMandiri'])
    ->name('pernikahan.store.layanan-mandiri');

// Layanan Mandiri (Public)
Route::prefix('layanan-mandiri')->group(function () {
    Route::get('/', [PageController::class, 'layananMandiri'])
        ->name('layanan-mandiri')
        ->middleware('camera.policy');
    Route::get('/{jenis_layanan}', [PageController::class, 'formLayanan'])
        ->name('layanan-mandiri.form');
    Route::post('/{jenis_layanan}', [PageController::class, 'submitLayanan'])
        ->name('layanan-mandiri.submit');
});

// Layanan Pernikahan (Public untuk testing - TODO: tambahkan middleware(['auth']) setelah testing selesai)
Route::prefix('layanan-mandiri')->name('pernikahan.')->group(function () {
    Route::get('/pernikahan', [UserPernikahanController::class, 'index'])->name('index');
    Route::get('/pernikahan/create', [UserPernikahanController::class, 'create'])->name('create');
    Route::post('/pernikahan', [UserPernikahanController::class, 'store'])->name('store');
    Route::get('/pernikahan/{id}', [UserPernikahanController::class, 'show'])->name('show');
});

// API untuk cek status pernikahan berdasarkan nomor antrian
Route::get('/api/pernikahan/status/{nomor_antrian}', [UserPernikahanController::class, 'getStatusByNomorAntrian'])->name('api.pernikahan.status');


Route::post('/kk/store/ubah-data', [KartKeluargaController::class, 'store_perubahan_data'])->name('kk.store');
Route::post('/kk/store/ganti-kepala-keluarga', [KartKeluargaController::class, 'store_ganti_kepala_kk'])->name('kk.store.gantikepalakk');
Route::post('/kk/store/kk_hilang_rusak', [KartKeluargaController::class, 'store_kk_hilang_rusak'])->name('kk.store.hilangrusak');
Route::post('/kk/store/pisah_kk', [KartKeluargaController::class, 'store_pisah_kk'])->name('kk.store.pisahkk');

Route::post('/akte-kematian/store', [AkteKematianController::class, 'store'])->name('akte-kematian.store');
Route::post('/lahir-mati/store', [LahirMatiController::class, 'store'])->name('lahir-mati.store');
Route::post('/penerbitan-akte-kelahiran-pengguna/store',[AkteLahirController::class, 'store'])->name('aktelahir.store');
// Statistik/Data Publik
Route::get('/statistik', [PageController::class, 'statistik'])->name('statistik');

// Halaman profil
Route::get('/profil', [Pengguna_Controller::class, 'profil'])->name('profil');

// Halaman berita
Route::get('/berita', [Pengguna_Controller::class, 'berita'])->name('berita');

// Halaman layanan
Route::get('/layanan', [Pengguna_Controller::class, 'layanan'])->name('layanan');

// Halaman kontak
Route::get('/kontak', [Pengguna_Controller::class, 'kontak'])->name('kontak');

// Halaman tracking/lacak
Route::get('/tracking', [Pengguna_Controller::class, 'tracking'])->name('tracking');

// Halaman organisasi (public)
Route::get('/organisasi', [UserOrganisasiController::class, 'index'])->name('organisasi');

// Halaman visi misi (public)
Route::get('/visi-misi', function () {
    return view('pages.visi-misi');
})->name('visi-misi');

/*
|--------------------------------------------------------------------------
| OCR ROUTES (KTP Scanner)
|--------------------------------------------------------------------------
*/

Route::prefix('ocr')->group(function () {
    // Halaman upload OCR (public)
    Route::get('/', function () {
        return view('ocr.index');
    })->name('ocr.index');
    
    // API endpoint dipindahkan ke api.php
});

// Admin OCR Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin/ocr')->name('admin.ocr.')->group(function () {
    Route::get('/result/{antrianId}', [\App\Http\Controllers\Api\EasyOcrController::class, 'showResult'])
        ->name('result')
        ->where('antrianId', '[a-f0-9-]+');
});

/*
|--------------------------------------------------------------------------
| SECURE FILE ROUTES (Authenticated file serving)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('secure-files')->group(function () {
    Route::get('/{path}', [SecureFileController::class, 'serve'])->name('secure-files.serve')->where('path', '.*');
    Route::get('/{path}/info', [SecureFileController::class, 'fileInfo'])->name('secure-files.info')->where('path', '.*');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

// Login routes (public access)
Route::get('login', [Login_Controller::class, 'tampilkan_form_login'])->name('login');
Route::post('login', [Login_Controller::class, 'proses_login'])->name('login.submit');

// Logout route - POST only untuk form, redirect GET ke home
Route::get('logout', function () {
    return redirect('/');
})->name('logout.get');

Route::post('logout', [Login_Controller::class, 'proses_logout'])->name('logout')->middleware('auth');

// Pengajuan Status (D7 - Feedback after Akta Kematian submission)
Route::get('/pengajuan/status/{id}', [PengajuanController::class, 'status'])
    ->name('pengajuan.status');

// Lacak Berkas route
Route::get('/lacak-berkas', [Antrian_Online_Controller::class, 'Lacak_Berkas'])->name('lacak.berkas');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (Membutuhkan authentication)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {
    // Admin Registrasi (hanya jika belum ada admin)
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [RegisterController::class, 'register'])
        ->middleware('throttle:3,1')
        ->name('admin.register.submit');

    // Verifikasi Pertanyaan Keamanan
    // GET tanpa {user_id} — user_id disimpan di session untuk mencegah
    // user enumeration via manipulasi URL (CWE-639 IDOR).
    Route::get('/verify', [Login_Controller::class, 'showVerifyQuestion'])->name('admin.verify.question');
    Route::post('/verify', [RegisterController::class, 'verifySecurityQuestion'])
        ->middleware('throttle:10,1')
        ->name('admin.verify.submit');

    // Admin Dashboard & Pages (membutuhkan auth)
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [Admin_Controller::class, 'dashboard'])->name('admin.dashboard');

        // Manajemen Konten - Berita
        Route::get('/berita', [Berita_Controller::class, 'index'])->name('admin.berita');
        Route::post('/berita', [Berita_Controller::class, 'store'])->name('admin.berita.store');
        Route::put('/berita/{berita}', [Berita_Controller::class, 'update'])->name('admin.berita.update');
        Route::delete('/berita/{berita}', [Berita_Controller::class, 'destroy'])->name('admin.berita.destroy');
        // Manajemen Organisasi
        Route::prefix('organisasi')->name('admin.organisasi.')->group(function () {
            Route::get('/', [AdminOrganisasiController::class, 'index'])->name('index');
            Route::post('/', [AdminOrganisasiController::class, 'store'])->name('store');
            Route::put('/{id}', [AdminOrganisasiController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminOrganisasiController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Dasar-Hukum
        Route::get('/dasar-hukum', [DasarHukumController::class, 'index'])->name('admin.dasar-hukum');
        Route::post('/dasar-hukum', [DasarHukumController::class, 'store'])->name('admin.dasar-hukum.store');
        Route::put('/dasar-hukum/{id}', [DasarHukumController::class, 'update'])->name('admin.dasar-hukum.update');
        Route::delete('/dasar-hukum/{id}', [DasarHukumController::class, 'destroy'])->name('admin.dasar-hukum.destroy');


        //Manajemen Penghargaan
        Route::get('/penghargaan', [PenghargaanController::class, 'index'])->name('admin.penghargaan');
        Route::post('/penghargaan', [PenghargaanController::class, 'store'])->name('admin.penghargaan.store');
        Route::put('/penghargaan/{id}', [PenghargaanController::class, 'update'])->name('admin.penghargaan.update');
        Route::delete('/penghargaan/{id}', [PenghargaanController::class, 'destroy'])->name('admin.penghargaan.destroy');

        // Visualisasi Data
        Route::get('/visualisasi-data', [Admin_Controller::class, 'visualisasi_data'])->name('admin.visualisasi-data');

        // Kelola Layanan
        Route::prefix('antrian-online')->group(function () {
            Route::get('/', [Admin_Controller::class, 'antrian_online'])->name('admin.antrian-online');
            Route::get('/data', [Admin_Controller::class, 'Get_Data_Antrian'])->name('admin.antrian-online.data');
            Route::get('/statistics', [Admin_Controller::class, 'Get_Data_Antrian_Statistics'])->name('admin.antrian-online.statistics');
            Route::post('/terima/{uuid}', [Admin_Controller::class, 'Terima_Dokumen'])->name('admin.antrian-online.terima')->whereUuid('uuid');
            Route::post('/verifikasi/{uuid}', [Admin_Controller::class, 'Verifikasi_Data'])->name('admin.antrian-online.verifikasi')->whereUuid('uuid');
            Route::post('/cetak/{uuid}', [Admin_Controller::class, 'Proses_Cetak'])->name('admin.antrian-online.cetak')->whereUuid('uuid');
            Route::post('/selesai/{uuid}', [Admin_Controller::class, 'Siap_Pengambilan'])->name('admin.antrian-online.selesai')->whereUuid('uuid');
            Route::post('/update-berkas/{uuid}', [Admin_Controller::class, 'Update_Berkas'])->name('admin.antrian-online.update-berkas')->whereUuid('uuid');
            Route::get('/riwayat/{uuid}', [Admin_Controller::class, 'Get_Riwayat_Berkas'])->name('admin.antrian-online.riwayat')->whereUuid('uuid');
            Route::delete('/{uuid}', [Admin_Controller::class, 'Hapus_Antrian'])->name('admin.antrian-online.hapus')->whereUuid('uuid');
        });

        Route::get('/tracking-berkas', [Admin_Controller::class, 'tracking_berkas'])->name('admin.tracking-berkas');
        Route::get('/dokumen-upload', [Admin_Controller::class, 'dokumen_upload'])->name('admin.dokumen-upload');

        // Penerbitan Dokumen
        // Kartu Keluarga
        Route::prefix('penerbitan-kk')->group(function () {
            Route::get('/', [KartKeluargaController::class, 'daftar_kk'])->name('admin.penerbitan-kk');
            // Gabungan dari kedua versi
            Route::get('/detail/{uuid}/{jenis}', [KartKeluargaController::class, 'detail'])->name('admin.detail');
            Route::post('/{uuid}/{jenis}/status', [KartKeluargaController::class, 'updateStatus'])->name('admin.status');
            Route::post('/{uuid}/{jenis}/upload-berkas', [KartKeluargaController::class, 'uploadBerkasFinal'])->name('admin.kk.upload-berkas');
            Route::get('/admin/berkas/{uuid}/{jenis}/lihat/{field}',[KartKeluargaController::class, 'lihatBerkas'])->name('admin.lihat-berkas');
        }); 

        // Akte Kelahiran
        Route::get('/penerbitan-akte-lahir', [Admin_Controller::class, 'penerbitan_akte_lahir'])->name('admin.penerbitan-akte-lahir');
        Route::prefix('penerbitan-akte-lahir')->group(function(){
            Route::get('/', [AkteLahirController::class, 'daftar_aktelahir'])->name('admin.penerbitan-akte-lahir');
            Route::get('/detail/{uuid}',[AkteLahirController::class, 'detail'])->name('admin.detail.aktelahir');
            Route::post('/{uuid}/status',[AkteLahirController::class, 'updateStatus'])->name('admin.status.aktelahir');
            Route::post('/{uuid}/upload-berkas',[AkteLahirController::class, 'uploadBerkasFinal'])->name('admin.aktelahir.upload-berkas');
            Route::get('/admin/berkas/{uuid}/lihat/{field}',[AkteLahirController::class, 'lihatBerkas'])->name('admin.lihat-berkas');
        });

        // Penerbitan Akte Kematian
        Route::prefix('penerbitan-akte-kematian')->group(function () {
            Route::get('/', [AkteKematianController::class, 'daftar'])->name('admin.penerbitan-akte-kematian');
            Route::get('/detail/{uuid}', [AkteKematianController::class, 'detail'])->name('admin.akte-kematian.detail');
            Route::post('/{uuid}/status', [AkteKematianController::class, 'updateStatus'])->name('admin.akte-kematian.status');
            Route::post('/{uuid}/upload-berkas', [AkteKematianController::class, 'uploadBerkasFinal'])->name('admin.akte-kematian.upload-berkas');
            Route::get('/admin/berkas/{uuid}/lihat/{field}', [AkteKematianController::class, 'lihatBerkas'])->name('admin.lihat-berkas-kematian');
        });

        // Penerbitan Lahir Mati
        Route::prefix('penerbitan-lahir-mati')->group(function () {
            Route::get('/', [LahirMatiController::class, 'daftar'])->name('admin.penerbitan-lahir-mati');
            Route::get('/detail/{uuid}', [LahirMatiController::class, 'detail'])->name('admin.lahir-mati.detail');
            Route::post('/{uuid}/status', [LahirMatiController::class, 'updateStatus'])->name('admin.lahir-mati.status');
            Route::post('/{uuid}/upload-berkas', [LahirMatiController::class, 'uploadBerkasFinal'])->name('admin.lahir-mati.upload-berkas');
            Route::get('/berkas/{uuid}/lihat/{field}', [LahirMatiController::class, 'lihatBerkas'])->name('admin.lihat-berkas-lahir-mati');
        });

        // Akte Kelahiran
        Route::prefix('penerbitan-akte-lahir')->group(function(){
            Route::get('/', [AkteLahirController::class, 'daftar_aktelahir'])->name('admin.penerbitan-akte-lahir');
            Route::get('/detail/{uuid}',[AkteLahirController::class, 'detail'])->name('admin.detail.aktelahir');
            Route::post('/{uuid}/status',[AkteLahirController::class, 'updateStatus'])->name('admin.status.aktelahir');
            Route::get('/berkas/{uuid}/lihat/{field}',[AkteLahirController::class, 'lihatBerkas'])->name('admin.lihat-berkas-akta-lahir');
        });
        Route::get('/penerbitan-pernikahan', [Admin_Controller::class, 'penerbitan_pernikahan'])->name('admin.penerbitan-pernikahan');

        // Layanan Pernikahan
        Route::prefix('pernikahan')->name('admin.pernikahan.')->group(function () {
            Route::get('/', [AdminPernikahanController::class, 'index'])->name('index');
            Route::get('/calendar-data', [AdminPernikahanController::class, 'calendarData'])->name('calendar-data');
            Route::post('/detail-ajax', [AdminPernikahanController::class, 'detailAjax'])->name('detail-ajax');
            Route::post('/{id}/verifikasi', [AdminPernikahanController::class, 'verifikasi'])->name('verifikasi')->whereUuid('id');
            Route::post('/{id}/proses', [AdminPernikahanController::class, 'prosesBerkas'])->name('proses')->whereUuid('id');
            Route::post('/{id}/approve-tanggal', [AdminPernikahanController::class, 'approveTanggal'])->name('approve-tanggal')->whereUuid('id');
            Route::post('/{id}/reject-tanggal', [AdminPernikahanController::class, 'rejectTanggal'])->name('reject-tanggal')->whereUuid('id');
            Route::post('/{id}/upload-dokumen-final', [AdminPernikahanController::class, 'uploadDokumenFinal'])->name('upload-dokumen-final')->whereUuid('id');
            Route::get('/{id}', [AdminPernikahanController::class, 'show'])->name('show')->whereUuid('id');
        });

        // Manajemen Akun
        // Ganti admin.manajemen_akun menjadi admin.manajemen-akun
        Route::get('/manajemen-akun', [Admin_Controller::class, 'manajemen_akun'])->name('admin.manajemen-akun');

        // Route untuk memproses simpan (Pastikan NAME ini sama dengan yang ada di ACTION FORM HTML)
        Route::post('/manajemen-akun/store', [Admin_Controller::class, 'store_akun'])->name('admin.manajemen-akun.store');

        // API Routes untuk Admin
        Route::get('/api/total-akun', [Admin_Controller::class, 'getTotalAkun'])->name('admin.api.total-akun');
        Route::get('/api/chart-antrian', [Admin_Controller::class, 'getChartAntrian'])->name('admin.api.chart-antrian');
    
        /*
        |--------------------------------------------------------------------------
        | STATISTIK ROUTES
        |--------------------------------------------------------------------------
        */
        
        // Statistik Penduduk
        Route::middleware(['permission:view statistik|edit statistik|delete statistik'])->prefix('statistik-penduduk')->name('admin.statistik-penduduk.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StatistikPendudukController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\StatistikPendudukController::class, 'store'])->name('store')->middleware('permission:create statistik');
            Route::put('/{id}', [App\Http\Controllers\Admin\StatistikPendudukController::class, 'update'])->name('update')->middleware('permission:edit statistik');
            Route::delete('/{id}', [App\Http\Controllers\Admin\StatistikPendudukController::class, 'destroy'])->name('destroy')->middleware('permission:delete statistik');
        });
        
        // Statistik Dokumen
        Route::middleware(['permission:view statistik|edit statistik|delete statistik'])->prefix('statistik-dokumen')->name('admin.statistik-dokumen.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StatistikDokumenController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\StatistikDokumenController::class, 'store'])->name('store')->middleware('permission:create statistik');
            Route::put('/{id}', [App\Http\Controllers\Admin\StatistikDokumenController::class, 'update'])->name('update')->middleware('permission:edit statistik');
            Route::delete('/{id}', [App\Http\Controllers\Admin\StatistikDokumenController::class, 'destroy'])->name('destroy')->middleware('permission:delete statistik');
            Route::post('/generate', [App\Http\Controllers\Admin\StatistikDokumenController::class, 'generate'])->name('generate')->middleware('permission:generate statistik');
        });
        
        // Statistik Layanan Bulanan
        Route::middleware(['permission:view statistik|edit statistik|delete statistik'])->prefix('statistik-layanan')->name('admin.statistik-layanan.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StatistikLayananController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\StatistikLayananController::class, 'store'])->name('store')->middleware('permission:create statistik');
            Route::put('/{id}', [App\Http\Controllers\Admin\StatistikLayananController::class, 'update'])->name('update')->middleware('permission:edit statistik');
            Route::delete('/{id}', [App\Http\Controllers\Admin\StatistikLayananController::class, 'destroy'])->name('destroy')->middleware('permission:delete statistik');
            Route::post('/generate', [App\Http\Controllers\Admin\StatistikLayananController::class, 'generate'])->name('generate')->middleware('permission:generate statistik');
        });
    
    });
});

/*
|--------------------------------------------------------------------------
| KEAGAMAAN ROUTES (Membutuhkan authentication role keagamaan)
|--------------------------------------------------------------------------
*/

Route::prefix('keagamaan')->name('keagamaan.')->group(function () {
    // Dashboard & Pages (membutuhkan auth)
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Keagamaan\DashboardController::class, 'index'])->name('dashboard');

        // Layanan Pernikahan
        Route::prefix('pernikahan')->name('pernikahan.')->group(function () {
            Route::get('/', [KeagamaanPernikahanController::class, 'index'])->name('index');
            Route::get('/request-tanggal', [KeagamaanPernikahanController::class, 'requestTanggal'])->name('request-tanggal');
            Route::get('/upload-berkas', [KeagamaanPernikahanController::class, 'uploadBerkas'])->name('upload-berkas');
            Route::get('/data', [KeagamaanPernikahanController::class, 'getData'])->name('data');
            Route::get('/available-jemaat', [KeagamaanPernikahanController::class, 'getAvailableJemaat'])->name('available-jemaat');
            Route::post('/submit-request-tanggal', [KeagamaanPernikahanController::class, 'submitRequestTanggal'])->name('submit-request-tanggal');
            Route::get('/check-updates', [KeagamaanPernikahanController::class, 'checkUpdates'])->name('check-updates');
            Route::get('/detail-dokumen/{id}', [KeagamaanPernikahanController::class, 'detailDokumen'])->name('detail-dokumen')->whereUuid('id');
            Route::get('/print-berkas/{id}', [KeagamaanPernikahanController::class, 'printBerkas'])->name('print-berkas')->whereUuid('id');
            Route::post('/detail-ajax', [KeagamaanPernikahanController::class, 'detailAjax'])->name('detail-ajax');
            Route::post('/upload-berkas-post', [KeagamaanPernikahanController::class, 'uploadBerkasPost'])->name('upload-berkas-post');
            Route::post('/{id}/konfirmasi-jemaat', [KeagamaanPernikahanController::class, 'konfirmasiJemaat'])->name('konfirmasi-jemaat')->whereUuid('id');
            Route::post('/{id}/set-tanggal', [KeagamaanPernikahanController::class, 'setTanggal'])->name('set-tanggal')->whereUuid('id');
            Route::post('/{id}/upload-dokumen', [KeagamaanPernikahanController::class, 'uploadDokumen'])->name('upload')->whereUuid('id');
            Route::get('/{id}', [KeagamaanPernikahanController::class, 'show'])->name('show')->whereUuid('id');
        });
    });
});

/*
|--------------------------------------------------------------------------
| STATISTIK PUBLIK ROUTES (Tanpa Autentikasi)
|--------------------------------------------------------------------------
*/

Route::prefix('statistik')->group(function () {
    // Halaman Statistik Publik
    Route::get('/', [StatistikPublikController::class, 'index'])->name('statistik');

    // API Data Statistik (untuk AJAX/Chart)
    Route::get('/data/penduduk', [StatistikPublikController::class, 'penduduk'])->name('statistik.data.penduduk');
    Route::get('/data/penduduk/trend', [StatistikPublikController::class, 'pendudukTrend'])->name('statistik.data.penduduk.trend');
    Route::get('/data/dokumen', [StatistikPublikController::class, 'dokumen'])->name('statistik.data.dokumen');
    Route::get('/data/layanan', [StatistikPublikController::class, 'layanan'])->name('statistik.data.layanan');
    Route::get('/data/combo', [StatistikPublikController::class, 'combo'])->name('statistik.data.combo');
    Route::get('/data/kecamatan', [StatistikPublikController::class, 'kecamatan'])->name('statistik.data.kecamatan');
    Route::get('/data/tahun', [StatistikPublikController::class, 'tahunTersedia'])->name('statistik.data.tahun');
});
