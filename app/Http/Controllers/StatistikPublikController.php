<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use App\Models\StatistikDokumen;
use App\Models\StatistikLayananBulanan;
use App\Models\StatistikPenduduk;
use App\Services\StatistikDokumenService;
use App\Services\StatistikLayananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * StatistikPublikController
 *
 * Controller untuk halaman dan data statistik publik (tanpa autentikasi)
 * Digunakan untuk visualisasi data di halaman publik
 */
class StatistikPublikController extends Controller
{
    public function __construct(
        private readonly StatistikDokumenService $statistikDokumenService,
        private readonly StatistikLayananService $statistikLayananService
    ) {}

    /**
     * Halaman statistik publik
     * GET /statistik
     */
    public function index(): View
    {
        // Data untuk halaman statistik publik
        $tahunSekarang = date('Y');
        $tahunTersedia = StatistikPenduduk::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->take(5)
            ->values();

        // Ringkasan data tahun berjalan
        $ringkasanPenduduk = [
            'total' => StatistikPenduduk::where('tahun', $tahunSekarang)->sum('total_penduduk'),
            'kecamatan' => StatistikPenduduk::where('tahun', $tahunSekarang)->count(),
        ];

        $ringkasanDokumen = [
            'total' => StatistikDokumen::where('tahun', $tahunSekarang)->sum('total_dokumen'),
        ];

        $ringkasanLayanan = [
            'total_antrian' => StatistikLayananBulanan::where('tahun', $tahunSekarang)->sum('total_antrian'),
            'total_selesai' => StatistikLayananBulanan::where('tahun', $tahunSekarang)->sum('antrian_selesai'),
        ];

        return view('pages.statistik', [
            'tahunSekarang' => $tahunSekarang,
            'tahunTersedia' => $tahunTersedia,
            'ringkasanPenduduk' => $ringkasanPenduduk,
            'ringkasanDokumen' => $ringkasanDokumen,
            'ringkasanLayanan' => $ringkasanLayanan,
        ]);
    }

    /**
     * Get statistik penduduk per kecamatan
     * GET /api/statistik/penduduk
     */
    public function penduduk(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $data = StatistikPenduduk::with('kecamatan')
            ->where('tahun', $tahun)
            ->get()
            ->sortBy(fn($item) => $item->kecamatan->nama_kecamatan ?? '')
            ->map(fn($item) => [
                'kecamatan_id' => $item->kecamatan_id,
                'kode_kecamatan' => $item->kecamatan->kode_kecamatan ?? null,
                'nama_kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                'tahun' => $item->tahun,
                'total_penduduk' => $item->total_penduduk,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'total_penduduk' => $data->sum('total_penduduk'),
            'data' => $data,
        ]);
    }

    /**
     * Get statistik penduduk per tahun (trend)
     * GET /api/statistik/penduduk/trend
     */
    public function pendudukTrend(Request $request): JsonResponse
    {
        $tahunAwal = $request->input('tahun_awal', date('Y') - 5);
        $tahunAkhir = $request->input('tahun_akhir', date('Y'));

        $data = StatistikPenduduk::selectRaw('tahun, SUM(total_penduduk) as total_penduduk')
            ->whereBetween('tahun', [$tahunAwal, $tahunAkhir])
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(fn($item) => [
                'tahun' => $item->tahun,
                'total_penduduk' => (int) $item->total_penduduk,
            ]);

        return response()->json([
            'success' => true,
            'tahun_awal' => $tahunAwal,
            'tahun_akhir' => $tahunAkhir,
            'data' => $data,
        ]);
    }

    /**
     * Get statistik dokumen bulanan
     * GET /api/statistik/dokumen
     */
    public function dokumen(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));
        
        $data = StatistikDokumen::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get()
            ->map(fn($item) => [
                'bulan' => $item->bulan,
                'nama_bulan' => $item->nama_bulan,
                'jumlah_kk' => $item->jumlah_kk,
                'jumlah_akte_lahir' => $item->jumlah_akte_lahir,
                'jumlah_akte_kematian' => $item->jumlah_akte_kematian,
                'jumlah_ktp' => $item->jumlah_ktp,
                'jumlah_kia' => $item->jumlah_kia,
                'total_dokumen' => $item->total_dokumen,
            ]);

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'total_dokumen' => $data->sum('total_dokumen'),
            'data' => $data,
        ]);
    }

    /**
     * Get ringkasan dokumen untuk dashboard publik
     * GET /api/statistik/dokumen/ringkasan
     */
    public function dokumenRingkasan(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $ringkasan = $this->statistikDokumenService->getRingkasan((int) $tahun);

        return response()->json([
            'success' => true,
            'data' => $ringkasan,
        ]);
    }

    /**
     * Get statistik layanan bulanan
     * GET /api/statistik/layanan
     */
    public function layanan(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));
        
        $data = StatistikLayananBulanan::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get()
            ->map(fn($item) => [
                'bulan' => $item->bulan,
                'nama_bulan' => $item->nama_bulan,
                'total_antrian' => $item->total_antrian,
                'antrian_menunggu' => $item->antrian_menunggu,
                'antrian_diproses' => $item->antrian_diproses,
                'antrian_selesai' => $item->antrian_selesai,
                'antrian_ditolak' => $item->antrian_ditolak,
                'waktu_avg_menit' => $item->waktu_avg_penanganan_menit,
                'persentase_kepuasan' => $item->persentase_kepuasan,
            ]);

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'total_antrian' => $data->sum('total_antrian'),
            'total_selesai' => $data->sum('antrian_selesai'),
            'data' => $data,
        ]);
    }

    /**
     * Get ringkasan layanan untuk dashboard publik
     * GET /api/statistik/layanan/ringkasan
     */
    public function layananRingkasan(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $ringkasan = $this->statistikLayananService->getRingkasan((int) $tahun);

        return response()->json([
            'success' => true,
            'data' => $ringkasan,
        ]);
    }

    /**
     * Get tren layanan bulanan (untuk chart)
     * GET /api/statistik/layanan/tren
     */
    public function layananTren(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $tren = $this->statistikLayananService->getTrenBulanan((int) $tahun);

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'data' => $tren,
        ]);
    }

    /**
     * Get data combo untuk semua statistik
     * GET /api/statistik/combo
     */
    public function combo(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        // Ambil data dari semua sumber
        $penduduk = StatistikPenduduk::where('tahun', $tahun)
            ->with('kecamatan')
            ->get();
        
        $dokumen = StatistikDokumen::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();
        
        $layanan = StatistikLayananBulanan::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        return response()->json([
            'success' => true,
            'tahun' => $tahun,
            'penduduk' => [
                'total' => $penduduk->sum('total_penduduk'),
                'kecamatan_count' => $penduduk->count(),
                'data' => $penduduk->map(fn($item) => [
                    'kecamatan' => $item->kecamatan->nama_kecamatan ?? '-',
                    'total' => $item->total_penduduk,
                ]),
            ],
            'dokumen' => [
                'total' => $dokumen->sum('total_dokumen'),
                'bulan_count' => $dokumen->count(),
                'by_jenis' => [
                    'kk' => $dokumen->sum('jumlah_kk'),
                    'akte_lahir' => $dokumen->sum('jumlah_akte_lahir'),
                    'akte_kematian' => $dokumen->sum('jumlah_akte_kematian'),
                    'ktp' => $dokumen->sum('jumlah_ktp'),
                    'kia' => $dokumen->sum('jumlah_kia'),
                ],
            ],
            'layanan' => [
                'total_antrian' => $layanan->sum('total_antrian'),
                'total_selesai' => $layanan->sum('antrian_selesai'),
                'avg_kepuasan' => round($layanan->avg('persentase_kepuasan'), 2),
                'bulan_count' => $layanan->count(),
            ],
        ]);
    }

    /**
     * Get daftar kecamatan
     * GET /api/statistik/kecamatan
     */
    public function kecamatan(): JsonResponse
    {
        $data = Kecamatan::urutkanNama()
            ->get()
            ->map(fn($item) => [
                'kecamatan_id' => $item->kecamatan_id,
                'kode_kecamatan' => $item->kode_kecamatan,
                'nama_kecamatan' => $item->nama_kecamatan,
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get daftar tahun yang tersedia
     * GET /api/statistik/tahun
     */
    public function tahunTersedia(): JsonResponse
    {
        $tahunPenduduk = StatistikPenduduk::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $tahunDokumen = StatistikDokumen::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $tahunLayanan = StatistikLayananBulanan::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        $allTahun = $tahunPenduduk->merge($tahunDokumen)->merge($tahunLayanan)->unique()->sortDesc()->values();

        return response()->json([
            'success' => true,
            'data' => $allTahun,
        ]);
    }
}
