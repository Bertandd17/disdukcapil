<?php

namespace App\Http\Controllers;

use App\Models\BeritaModel;
use App\Models\DasarHukum;
use App\Models\Penghargaan;
use Illuminate\Http\Request;
use App\Models\OrganisasiModel;

class PageController extends Controller
{
    /**
     * Halaman Beranda / Home
     */
    public function index()
    {
        $beritas = BeritaModel::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $dasarHukum = DasarHukum::orderBy('created_at', 'desc')->get();
        $penghargaan = Penghargaan::orderBy('created_at', 'desc')->get();
        $newsForModal = $beritas->keyBy->id->map(function ($b) {
            $tanggal = ($b->published_at ?? $b->created_at)->locale('id')->translatedFormat('d F Y');

            return [
                'category' => $b->judul,
                'date' => $tanggal,
                'title' => $b->judul,
                'content' => $b->konten,
            ];
        });

        // Ambil data organisasi untuk halaman publik - dikelompokkan berdasarkan level
        $organisasiByLevel = [
            'pimpinan_utama' => OrganisasiModel::byLevel('pimpinan_utama')->get(),
            'sub_bagian' => OrganisasiModel::byLevel('sub_bagian')->get(),
            'kelompok_fungsional_sekretariat' => OrganisasiModel::query()
                ->where('level', 'kelompok_fungsional')
                ->where('urutan', '<=', 7)
                ->orderBy('urutan')
                ->get(),
            'bidang' => OrganisasiModel::byLevel('bidang')->get(),
            'koordinator' => OrganisasiModel::byLevel('koordinator')->get(),
            'kelompok_fungsional_bidang' => OrganisasiModel::query()
                ->where('level', 'kelompok_fungsional')
                ->where('urutan', '>', 7)
                ->orderBy('urutan')
                ->get(),
        ];

        return view('pages.index', compact('beritas', 'newsForModal','dasarHukum','penghargaan','organisasiByLevel'));
    }

    /**
     * Halaman Layanan Mandiri
     */
    public function layananMandiri()
    {
                return response()
            ->view('pages.layanan-mandiri')
            ->header('Permissions-Policy', 'camera=(self)')
            ->header('Feature-Policy', 'camera *');
    }


public function unduhFormulir()
    {
        return view('pages.unduh-formulir');
    }
    /**
     * Form Layanan Mandiri per jenis
     */
    public function formLayanan($jenis_layanan)
    {
        $services = [
            'ktp' => 'KTP Elektronik',
            'kk' => 'Kartu Keluarga',
            'akta-lahir' => 'Akta Kelahiran',
            'akta-kematian' => 'Akta Kematian',
            'kia' => 'Kartu Identitas Anak',
            'pindah' => 'Surat Pindah',
            'kawin' => 'Akta Perkawinan',
            'cerai' => 'Akta Perceraian',
        ];

        if (!isset($services[$jenis_layanan])) {
            abort(404);
        }

        return view('pages.form-layanan', [
            'jenis_layanan' => $jenis_layanan,
            'nama_layanan' => $services[$jenis_layanan]
        ]);
    }

    /**
     * Submit Layanan Mandiri
     */
    public function submitLayanan(Request $request, $jenis_layanan)
    {
        // DEBUG: Log untuk tracking request
        \Log::info('=== PageController::submitLayanan CALLED ===', [
            'jenis_layanan' => $jenis_layanan,
            'url' => $request->fullUrl(),
            'all_input_keys' => array_keys($request->all()),
        ]);

        // Validate request
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
        ]);

        // Simpan logic ke database disini
        // ...

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan layanan berhasil dikirim. Silakan pantau status secara berkala.',
            ]);
        }

        return redirect()->route('layanan-mandiri')
            ->with('success', 'Pengajuan layanan berhasil dikirim. Silakan pantau status secara berkala.');
    }

    /**
     * Halaman Statistik / Visualisasi Data
     */
    public function statistik()
    {
        // Data statistik
        $stats = [
            'total_penduduk' => 250487,
            'ktp_elektronik' => 238210,
            'kartu_keluarga' => 78456,
            'kia_anak' => 45234
        ];

        $districts = [
            ['name' => 'Kec. Balige', 'penduduk' => 45234, 'kk' => 12456, 'ktp' => 43120, 'percentage' => 95],
            ['name' => 'Kec. Borbor', 'penduduk' => 28456, 'kk' => 7890, 'ktp' => 27340, 'percentage' => 92],
            ['name' => 'Kec. Laguboti', 'penduduk' => 35678, 'kk' => 9234, 'ktp' => 34120, 'percentage' => 94],
        ];

        return view('pages.statistik', compact('stats', 'districts'));
    }

}
