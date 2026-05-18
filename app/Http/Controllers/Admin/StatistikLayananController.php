<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatistikLayananRequest;
use App\Models\StatistikLayananBulanan;
use App\Services\StatistikLayananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StatistikLayananController extends Controller
{
    public function __construct(
        private readonly StatistikLayananService $statistikService
    ) {}

    /**
     * Display a listing of statistik layanan.
     */
    public function index(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        // Ambil daftar tahun yang tersedia
        $tahunTersedia = StatistikLayananBulanan::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
        
        // Pastikan ada tahun saat ini
        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([date('Y')]);
        }
        if (!$tahunTersedia->contains(date('Y'))) {
            $tahunTersedia->push(date('Y'));
            $tahunTersedia = $tahunTersedia->sort()->reverse()->values();
        }

        // Summary
        $statistik = StatistikLayananBulanan::where('tahun', $tahun)->get();
        
        $summary = [
            'total_antrian' => $statistik->sum('total_antrian'),
            'total_selesai' => $statistik->sum('antrian_selesai'),
            'total_menunggu' => $statistik->sum('antrian_menunggu'),
            'total_diproses' => $statistik->sum('antrian_diproses'),
            'total_ditolak' => $statistik->sum('antrian_ditolak'),
            'avg_waktu' => (int) round($statistik->avg('waktu_avg_penanganan_menit') ?? 0),
            'avg_kepuasan' => round($statistik->avg('persentase_kepuasan') ?? 0, 2),
        ];

        // Ambil data
        $data = StatistikLayananBulanan::where('tahun', $tahun)->urutPeriode()->get();

        return view('admin.statistik.layanan.index', [
            'tahun' => $tahun,
            'tahunTersedia' => $tahunTersedia,
            'summary' => $summary,
            'data' => $data,
            'canCreate' => auth()->user()->can('create statistik'),
            'canEdit' => auth()->user()->can('edit statistik'),
            'canDelete' => auth()->user()->can('delete statistik'),
            'canGenerate' => auth()->user()->can('generate statistik'),
        ]);
    }

    /**
     * Show the form for creating new statistik layanan (redirect to index).
     */
    public function create()
    {
        return redirect()->route('admin.statistik-layanan.index');
    }

    /**
     * Store a newly created statistik layanan.
     */
    public function store(StatistikLayananRequest $request): JsonResponse
    {
        try {
            // Cek apakah data sudah ada
            $exists = StatistikLayananBulanan::where('tahun', $request->tahun)
                ->where('bulan', $request->bulan)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk periode ini sudah ada. Gunakan fitur edit untuk mengubah data.',
                ], 422);
            }

            $statistik = new StatistikLayananBulanan();
            $statistik->statistik_layanan_bulanan_id = Str::uuid()->toString();
            $statistik->tahun = $request->tahun;
            $statistik->bulan = $request->bulan;
            $statistik->antrian_menunggu = $request->antrian_menunggu ?? 0;
            $statistik->antrian_diproses = $request->antrian_diproses ?? 0;
            $statistik->antrian_selesai = $request->antrian_selesai ?? 0;
            $statistik->antrian_ditolak = $request->antrian_ditolak ?? 0;
            $statistik->waktu_avg_penanganan_menit = $request->waktu_avg_penanganan_menit ?? 0;
            $statistik->persentase_kepuasan = $request->persentase_kepuasan ?? 0;
            $statistik->is_auto_generated = false;
            $statistik->save();

            Log::info('Admin: Statistik layanan berhasil dibuat (manual)', [
                'id' => $statistik->statistik_layanan_bulanan_id,
                'periode' => $statistik->periode_format,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik layanan berhasil disimpan.',
                'data' => $statistik,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal menyimpan statistik layanan', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
            ], 500);
        }
    }

    /**
     * Show the form for editing statistik layanan (redirect to index).
     */
    public function edit(string $id)
    {
        return redirect()->route('admin.statistik-layanan.index');
    }

    /**
     * Update the specified statistik layanan.
     */
    public function update(StatistikLayananRequest $request, string $id): JsonResponse
    {
        try {
            $statistik = StatistikLayananBulanan::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $exists = StatistikLayananBulanan::where('tahun', $request->tahun)
                ->where('bulan', $request->bulan)
                ->where('statistik_layanan_bulanan_id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk periode ini sudah ada.',
                ], 422);
            }

            $statistik->tahun = $request->tahun;
            $statistik->bulan = $request->bulan;
            $statistik->antrian_menunggu = $request->antrian_menunggu ?? 0;
            $statistik->antrian_diproses = $request->antrian_diproses ?? 0;
            $statistik->antrian_selesai = $request->antrian_selesai ?? 0;
            $statistik->antrian_ditolak = $request->antrian_ditolak ?? 0;
            $statistik->waktu_avg_penanganan_menit = $request->waktu_avg_penanganan_menit ?? 0;
            $statistik->persentase_kepuasan = $request->persentase_kepuasan ?? 0;
            $statistik->save();

            Log::info('Admin: Statistik layanan berhasil diupdate', [
                'id' => $statistik->statistik_layanan_bulanan_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik layanan berhasil diperbarui.',
                'data' => $statistik->fresh(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal update statistik layanan', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.',
            ], 500);
        }
    }

    /**
     * Remove the specified statistik layanan.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $statistik = StatistikLayananBulanan::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $statistik->delete();

            Log::info('Admin: Statistik layanan berhasil dihapus', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik layanan berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal hapus statistik layanan', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }

    /**
     * Generate statistik layanan secara otomatis.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'bulan' => 'nullable|integer|min:1|max:12',
            'bulan_awal' => 'nullable|integer|min:1|max:12',
            'bulan_akhir' => 'nullable|integer|min:1|max:12',
        ]);

        try {
            if ($request->bulan) {
                $result = $this->statistikService->generateDariAntrian(
                    (int) $request->tahun,
                    (int) $request->bulan
                );
            } elseif ($request->bulan_awal && $request->bulan_akhir) {
                $result = $this->statistikService->generateRange(
                    (int) $request->tahun,
                    (int) $request->bulan_awal,
                    (int) $request->bulan_akhir
                );
            } else {
                $result = $this->statistikService->generateTahunan((int) $request->tahun);
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal generate statistik layanan', [
                'tahun' => $request->tahun,
                'bulan' => $request->bulan ?? 'all',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat generate statistik.',
            ], 500);
        }
    }

    /**
     * Get ringkasan statistik untuk dashboard.
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $ringkasan = $this->statistikService->getRingkasan((int) $tahun);

        return response()->json([
            'success' => true,
            'data' => $ringkasan,
        ]);
    }

    /**
     * Get tren bulanan untuk chart.
     */
    public function tren(Request $request): JsonResponse
    {
        $tahun = $request->input('tahun', date('Y'));

        $tren = $this->statistikService->getTrenBulanan((int) $tahun);

        return response()->json([
            'success' => true,
            'data' => $tren,
        ]);
    }
}
