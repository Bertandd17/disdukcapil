<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatistikDokumenRequest;
use App\Models\StatistikDokumen;
use App\Services\StatistikDokumenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StatistikDokumenController extends Controller
{
    public function __construct(
        private readonly StatistikDokumenService $statistikService
    ) {}

    /**
     * Display a listing of statistik dokumen.
     */
    public function index(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));

        // Ambil daftar tahun yang tersedia
        $tahunTersedia = StatistikDokumen::select('tahun')
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
        $summary = [
            'total_dokumen' => StatistikDokumen::where('tahun', $tahun)->sum('total_dokumen'),
            'total_kk' => StatistikDokumen::where('tahun', $tahun)->sum('jumlah_kk'),
            'total_akte_lahir' => StatistikDokumen::where('tahun', $tahun)->sum('jumlah_akte_lahir'),
            'total_akte_kematian' => StatistikDokumen::where('tahun', $tahun)->sum('jumlah_akte_kematian'),
            'total_ktp' => StatistikDokumen::where('tahun', $tahun)->sum('jumlah_ktp'),
            'total_kia' => StatistikDokumen::where('tahun', $tahun)->sum('jumlah_kia'),
        ];

        // Ambil data
        $data = StatistikDokumen::where('tahun', $tahun)->urutPeriode()->get();

        return view('admin.statistik.dokumen.index', [
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
     * Show the form for creating new statistik dokumen (redirect to index).
     */
    public function create()
    {
        return redirect()->route('admin.statistik-dokumen.index');
    }

    /**
     * Store a newly created statistik dokumen.
     */
    public function store(StatistikDokumenRequest $request): JsonResponse
    {
        try {
            // Cek apakah data sudah ada
            $exists = StatistikDokumen::where('tahun', $request->tahun)
                ->where('bulan', $request->bulan)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk periode ini sudah ada. Gunakan fitur edit untuk mengubah data.',
                ], 422);
            }

            $statistik = new StatistikDokumen();
            $statistik->statistik_dokumen_id = Str::uuid()->toString();
            $statistik->tahun = $request->tahun;
            $statistik->bulan = $request->bulan;
            $statistik->jumlah_kk = $request->jumlah_kk ?? 0;
            $statistik->jumlah_akte_lahir = $request->jumlah_akte_lahir ?? 0;
            $statistik->jumlah_akte_kematian = $request->jumlah_akte_kematian ?? 0;
            $statistik->jumlah_ktp = $request->jumlah_ktp ?? 0;
            $statistik->jumlah_kia = $request->jumlah_kia ?? 0;
            $statistik->is_auto_generated = false;
            $statistik->save();

            Log::info('Admin: Statistik dokumen berhasil dibuat (manual)', [
                'id' => $statistik->statistik_dokumen_id,
                'periode' => $statistik->periode_format,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik dokumen berhasil disimpan.',
                'data' => $statistik,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal menyimpan statistik dokumen', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
            ], 500);
        }
    }

    /**
     * Show the form for editing statistik dokumen (redirect to index).
     */
    public function edit(string $id)
    {
        return redirect()->route('admin.statistik-dokumen.index');
    }

    /**
     * Update the specified statistik dokumen.
     */
    public function update(StatistikDokumenRequest $request, string $id): JsonResponse
    {
        try {
            $statistik = StatistikDokumen::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $exists = StatistikDokumen::where('tahun', $request->tahun)
                ->where('bulan', $request->bulan)
                ->where('statistik_dokumen_id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk periode ini sudah ada.',
                ], 422);
            }

            $statistik->tahun = $request->tahun;
            $statistik->bulan = $request->bulan;
            $statistik->jumlah_kk = $request->jumlah_kk ?? 0;
            $statistik->jumlah_akte_lahir = $request->jumlah_akte_lahir ?? 0;
            $statistik->jumlah_akte_kematian = $request->jumlah_akte_kematian ?? 0;
            $statistik->jumlah_ktp = $request->jumlah_ktp ?? 0;
            $statistik->jumlah_kia = $request->jumlah_kia ?? 0;
            $statistik->save();

            Log::info('Admin: Statistik dokumen berhasil diupdate', [
                'id' => $statistik->statistik_dokumen_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik dokumen berhasil diperbarui.',
                'data' => $statistik->fresh(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal update statistik dokumen', [
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
     * Remove the specified statistik dokumen.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $statistik = StatistikDokumen::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $statistik->delete();

            Log::info('Admin: Statistik dokumen berhasil dihapus', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik dokumen berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal hapus statistik dokumen', [
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
     * Generate statistik dokumen secara otomatis.
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
                // Generate satu bulan
                $result = $this->statistikService->generateBulanan(
                    (int) $request->tahun,
                    (int) $request->bulan
                );
            } elseif ($request->bulan_awal && $request->bulan_akhir) {
                // Generate range bulan
                $result = $this->statistikService->generateRange(
                    (int) $request->tahun,
                    (int) $request->bulan_awal,
                    (int) $request->bulan_akhir
                );
            } else {
                // Generate satu tahun
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
            Log::error('Admin: Gagal generate statistik dokumen', [
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
}
