<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use App\Models\User;
use App\Models\Layanan_Model;
use App\Models\Jenis_Keagamaan_Model;
use App\Models\Keagamaan_Model;
use App\Exceptions\DatabaseException;
use Illuminate\Http\Request;
use App\Models\Organisasi_Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Admin_Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tampilkan dashboard admin
     */
    public function dashboard()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.dashboard');
    }

    /**
     * Tampilkan halaman organisasi
     */
    public function organisasi()
    {
       if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Mengambil data dari tabel organisasi dan merubah key array menjadi kode_posisi
        $struktur = Organisasi::orderBy('urutan')->get()->keyBy('kode_posisi');

        return view('admin.organisasi', compact('struktur'));
    }

    public function update_organisasi(Request $request, $id)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Validasi input
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'nama_pejabat' => 'required|string|max:255',
            'eselon' => 'nullable|string|max:50',
        ]);

        // Cari data dan update
        $item = Organisasi::findOrFail($id);
        $item->update([
            'nama_jabatan' => $request->nama_jabatan,
            'nama_pejabat' => $request->nama_pejabat,
            'eselon' => $request->eselon
        ]);

        return redirect()->back()->with('success', 'Data Struktur Organisasi Berhasil Diperbarui!');
    }
    
    /**
    * Tampilkan halaman penghargaan
    */
    public function penghargaan()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penghargaan');
    }

    /**
     * Tampilkan halaman dasar hukum
     */
    public function dasar_hukum()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.dasar_hukum');
    }

    /**
     * Tampilkan halaman statistik
     */
    public function statistik()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.statistik');
    }

    /**
     * Tampilkan halaman visualisasi data
     */
    public function visualisasi_data()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.visualisasi-data');
    }

    /**
     * Tampilkan halaman antrian online
     */
    public function antrian_online()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.antrian-online-index');
    }

    /**
     * Tampilkan halaman kelola layanan
     */
    public function kelola_layanan()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.kelola_layanan');
    }

    /**
     * Tampilkan halaman manajemen akun
     */
    public function manajemen_akun()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Ambil user dengan role Keagamaan dan relasinya
        $users = User::role('Keagamaan')
            ->with('detail_keagamaan.jenis_keagamaan')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil semua jenis keagamaan
        $list_agama = Jenis_Keagamaan_Model::orderBy('nama_jenis_keagamaan')->get();

        return view('admin.manajemen_akun', compact('users', 'list_agama'));
    }

    public function store_akun(Request $request)
    {
        $isEdit = filled($request->accountId) && $request->accountId !== 'null';

        // Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($request->accountId),
            ],
            'agama' => 'required|exists:jenis_keagamaan,jenis_keagamaan_id',
            'alamat' => 'required|string',
            'status' => 'nullable|in:aktif,non-aktif',
            'password' => ($request->accountId ? 'nullable' : 'required') . '|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'agama.required' => 'Jenis keagamaan wajib dipilih.',
            'alamat.required' => 'Alamat wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        DB::beginTransaction();
        try {
            $id = $isEdit ? $request->accountId : null;

            if (!$id) {
                // PROSES TAMBAH BARU
                $user = new User();
                $user->password = Hash::make($request->password);
            } else {
                // PROSES EDIT
                $user = User::findOrFail($id);
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }
            }

            $user->name = $request->name;
            $user->username = $request->username;
            $user->save();

            // Sinkronisasi Role Spatie
            $user->syncRoles(['Keagamaan']);

            // Update Detail Keagamaan
            $user->detail_keagamaan()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'jenis_keagamaan_id' => $request->agama,
                    'alamat' => $request->alamat,
                    'status' => $id ? ($request->status ?? 'aktif') : 'aktif',
                ]
            );

            DB::commit();

            $message = $isEdit
                ? 'Akun keagamaan berhasil diperbarui!'
                : 'Akun keagamaan baru berhasil dibuat! Akun dapat langsung digunakan untuk login.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                    ],
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Admin: gagal menyimpan akun keagamaan', [
                'account_id' => $request->accountId,
                'username' => $request->username,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan akun.',
                    'detail' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Tampilkan halaman penerbitan KK
     */
    public function penerbitan_kk()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penerbitan_kk');
    }

    /**
     * Tampilkan halaman penerbitan akte lahir
     */
    public function penerbitan_akte_lahir()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penerbitan_akte_lahir');
    }

    /**
     * Tampilkan halaman penerbitan akte kematian
     */
    public function penerbitan_akte_kematian()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penerbitan_akte_kematian');
    }

    /**
     * Tampilkan halaman penerbitan lahir mati
     */
    public function penerbitan_lahir_mati()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penerbitan_lahir_mati');
    }

    /**
     * Tampilkan halaman penerbitan pernikahan
     */
    public function penerbitan_pernikahan()
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        return view('admin.penerbitan_pernikahan');
    }

    // ==================== ANTRIAN ONLINE FUNCTIONS ====================

    /**
     * Get data antrian online untuk admin
     * HANYA mengambil antrian dengan status "Menunggu"
     */
    public function Get_Data_Antrian(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        try {
            // HANYA ambil antrian dengan status "Menunggu"
            // Antrian yang sudah diproses akan hilang dari daftar ini
            $data_antrian = Antrian_Online_Model::where('status_antrian', 'Menunggu')
                ->orderBy('created_at', 'asc')
                ->get();

            // Load layanan data secara manual untuk setiap item
            foreach ($data_antrian as $antrian) {
                try {
                    $layanan = \App\Models\Layanan_Model::find($antrian->layanan_id);
                    $antrian->layanan = $layanan;
                } catch (\Exception $e) {
                    \Log::warning('Gagal load layanan untuk antrian ' . $antrian->antrian_online_id . ': ' . $e->getMessage());
                    $antrian->layanan = null;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data_antrian,
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Get_Data_Antrian Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get statistik antrian online untuk admin
     * Mengambil SEMUA data untuk menghitung statistik lengkap
     */
    public function Get_Data_Antrian_Statistics(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        try {
            // Ambil SEMUA antrian untuk statistik lengkap
            $all_antrian = Antrian_Online_Model::orderBy('created_at', 'desc')->get();

            // Hitung statistik
            $menungguCount = 0;
            $processingCount = 0;
            $completedCount = 0;

            foreach ($all_antrian as $antrian) {
                $status = $antrian->status_antrian;

                if ($status === 'Menunggu') {
                    $menungguCount++;
                } elseif (in_array($status, ['Dokumen Diterima', 'Verifikasi Data', 'Proses Cetak'])) {
                    $processingCount++;
                } elseif ($status === 'Siap Pengambilan') {
                    $completedCount++;
                }
            }

            $totalCount = $all_antrian->count();

            return response()->json([
                'success' => true,
                'menunggu' => $menungguCount,
                'processing' => $processingCount,
                'completed' => $completedCount,
                'total' => $totalCount,
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            \Log::error('Get_Data_Antrian_Statistics Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat statistik',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Terima dokumen (langkah pertama)
     */
    public function Terima_Dokumen($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Cek apakah status sudah Dokumen Diterima
        if ($antrian->status_antrian === 'Dokumen Diterima') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen sudah diterima sebelumnya',
            ], 400);
        }

        $antrian->update(['status_antrian' => 'Dokumen Diterima']);

        // Cek apakah sudah ada lacak berkas dengan status Dokumen Diterima
        $existing_lacak = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->where('status', 'Dokumen Diterima')
            ->first();

        // Hanya buat lacak berkas baru jika belum ada
        if (!$existing_lacak) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => 'Dokumen Diterima',
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Dokumen diterima, menunggu verifikasi',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diterima',
        ]);
    }

    /**
     * Mulai verifikasi data
     */
    public function Verifikasi_Data($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Cek apakah status sudah Verifikasi Data
        if ($antrian->status_antrian === 'Verifikasi Data') {
            return response()->json([
                'success' => false,
                'message' => 'Status sudah Verifikasi Data',
            ], 400);
        }

        $antrian->update(['status_antrian' => 'Verifikasi Data']);

        // Cek apakah sudah ada lacak berkas dengan status Verifikasi Data
        $existing_lacak = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->where('status', 'Verifikasi Data')
            ->first();

        // Hanya buat lacak berkas baru jika belum ada
        if (!$existing_lacak) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => 'Verifikasi Data',
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Data sedang diverifikasi oleh admin',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi data berhasil dimulai',
        ]);
    }

    /**
     * Proses cetak dokumen
     */
    public function Proses_Cetak($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Cek apakah status sudah Proses Cetak
        if ($antrian->status_antrian === 'Proses Cetak') {
            return response()->json([
                'success' => false,
                'message' => 'Status sudah Proses Cetak',
            ], 400);
        }

        $antrian->update(['status_antrian' => 'Proses Cetak']);

        // Cek apakah sudah ada lacak berkas dengan status Proses Cetak
        $existing_lacak = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->where('status', 'Proses Cetak')
            ->first();

        // Hanya buat lacak berkas baru jika belum ada
        if (!$existing_lacak) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => 'Proses Cetak',
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Dokumen sedang dalam proses cetak',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses cetak berhasil dimulai',
        ]);
    }

    /**
     * Siap diambil
     */
    public function Siap_Pengambilan($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Cek apakah status sudah Siap Pengambilan
        if ($antrian->status_antrian === 'Siap Pengambilan') {
            return response()->json([
                'success' => false,
                'message' => 'Status sudah Siap Pengambilan',
            ], 400);
        }

        $antrian->update(['status_antrian' => 'Siap Pengambilan']);

        // Cek apakah sudah ada lacak berkas dengan status Siap Pengambilan
        $existing_lacak = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->where('status', 'Siap Pengambilan')
            ->first();

        // Hanya buat lacak berkas baru jika belum ada
        if (!$existing_lacak) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => 'Siap Pengambilan',
                'tanggal' => date('Y-m-d'),
                'keterangan' => 'Dokumen siap diambil oleh pemohon',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dokumen siap diambil',
        ]);
    }

    /**
     * Update berkas antrian
     */
    public function Update_Berkas(Request $request, $uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $request->validate([
            'status' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
            'alasan_penolakan' => 'required_if:status,Ditolak|string|nullable',
        ]);

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Cek apakah status yang akan diupdate sama dengan status sekarang
        if ($antrian->status_antrian === $request->status) {
            return response()->json([
                'success' => false,
                'message' => 'Status antrian sama dengan status sekarang',
            ], 400);
        }

        // Update status antrian di tabel antrian_online
        $antrian->update(['status_antrian' => $request->status]);

        // Cek apakah sudah ada lacak berkas dengan status yang sama
        $existing_lacak = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->where('status', $request->status)
            ->first();

        // Hanya buat lacak berkas baru jika belum ada
        if (!$existing_lacak) {
            // Siapkan data untuk lacak berkas
            $dataLacak = [
                'antrian_online_id' => $antrian->antrian_online_id,
                'status' => $request->status,
                'tanggal' => date('Y-m-d'),
                'keterangan' => $request->keterangan ?? "Status diperbarui: {$request->status}",
            ];

            // Tambahkan alasan penolakan jika statusnya Ditolak
            if ($request->status === 'Ditolak' && $request->has('alasan_penolakan')) {
                $dataLacak['alasan_penolakan'] = $request->alasan_penolakan;
            }

            // Tambahkan riwayat lacak berkas baru
            Lacak_Berkas_Model::create($dataLacak);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berkas berhasil diperbarui',
        ]);
    }

    /**
     * Get riwayat lacak berkas
     */
    public function Get_Riwayat_Berkas($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();
        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        $riwayat_berkas = Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'riwayat' => $riwayat_berkas,
            ],
        ]);
    }

    /**
     * Hapus antrian
     */
    public function Hapus_Antrian($uuid)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $antrian = Antrian_Online_Model::where('antrian_online_id', $uuid)->first();

        if (!$antrian) {
            return response()->json([
                'success' => false,
                'message' => 'Antrian tidak ditemukan',
            ], 404);
        }

        // Hapus semua riwayat lacak berkas terkait
        Lacak_Berkas_Model::where('antrian_online_id', $antrian->antrian_online_id)->delete();

        // Hapus antrian
        $antrian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Antrian berhasil dihapus',
        ]);
    }

    /**
     * API: Get total akun admin dan keagamaan
     */
    public function getTotalAkun()
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['success' => false], 403);
        }

        $totalAdmin = User::role('Admin')->count();
        $totalKeagamaan = User::role('Keagamaan')->count();
        $total = $totalAdmin + $totalKeagamaan;

        return response()->json([
            'success' => true,
            'total' => $total,
            'total_admin' => $totalAdmin,
            'total_keagamaan' => $totalKeagamaan,
        ]);
    }

    /**
     * API: Get statistik antrian untuk chart
     */
    public function getChartAntrian(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['success' => false], 403);
        }

        $days = $request->get('days', 7);

        // Ambil data antrian berdasarkan periode
        $startDate = now()->subDays($days - 1)->startOfDay();
        $endDate = now()->endOfDay();

        $antrianData = Antrian_Online_Model::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status_antrian = "Menunggu" THEN 1 ELSE 0 END) as menunggu'),
            DB::raw('SUM(CASE WHEN status_antrian IN ("Dokumen Diterima", "Verifikasi Data", "Proses Cetak") THEN 1 ELSE 0 END) as proses'),
            DB::raw('SUM(CASE WHEN status_antrian = "Siap Pengambilan" THEN 1 ELSE 0 END) as selesai')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Generate labels dan data untuk chart
        $labels = [];
        $dataMenunggu = [];
        $dataProses = [];
        $dataSelesai = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data = $antrianData->firstWhere('date', $date);

            $labels[] = now()->subDays($i)->locale('id')->format('D/M');
            $dataMenunggu[] = $data ? $data->menunggu : 0;
            $dataProses[] = $data ? $data->proses : 0;
            $dataSelesai[] = $data ? $data->selesai : 0;
        }

        return response()->json([
            'success' => true,
            'labels' => $labels,
            'data' => [
                'menunggu' => $dataMenunggu,
                'proses' => $dataProses,
                'selesai' => $dataSelesai,
            ],
        ]);
    }
}
