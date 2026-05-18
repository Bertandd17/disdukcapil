<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatistikPendudukRequest;
use App\Models\Kecamatan;
use App\Models\StatistikPenduduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StatistikPendudukController extends Controller
{
    /**
     * Display a listing of statistik penduduk.
     */
    public function index(Request $request): View
    {
        $tahun = $request->input('tahun', date('Y'));
        $kecamatanId = $request->input('kecamatan_id');

        // Ambil daftar tahun yang tersedia
        $tahunTersedia = StatistikPenduduk::select('tahun')
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

        // Ambil daftar kecamatan
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        // Summary footer
        $summary = [
            'total_penduduk' => StatistikPenduduk::where('tahun', $tahun)->sum('total_penduduk'),
            'jumlah_kecamatan' => StatistikPenduduk::where('tahun', $tahun)->count(),
            'rata_rata' => 0,
        ];

        if ($summary['jumlah_kecamatan'] > 0) {
            $summary['rata_rata'] = round($summary['total_penduduk'] / $summary['jumlah_kecamatan']);
        }

        // Query data
        $query = StatistikPenduduk::with('kecamatan')
            ->where('tahun', $tahun);

        if ($kecamatanId) {
            $query->where('kecamatan_id', $kecamatanId);
        }

        $data = $query->orderBy('tahun', 'desc')->get();

        // Check permissions once for the view
        $canCreate = auth()->user()->can('create statistik');
        $canEdit = auth()->user()->can('edit statistik');
        $canDelete = auth()->user()->can('delete statistik');

        return view('admin.statistik.penduduk.index', [
            'tahun' => $tahun,
            'tahunTersedia' => $tahunTersedia,
            'kecamatan' => $kecamatan,
            'kecamatanId' => $kecamatanId,
            'summary' => $summary,
            'data' => $data,
            'canCreate' => $canCreate,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    /**
     * Store a newly created statistik penduduk.
     */
    public function store(StatistikPendudukRequest $request): JsonResponse
    {
        try {
            // Cek apakah data sudah ada
            $exists = StatistikPenduduk::where('kecamatan_id', $request->kecamatan_id)
                ->where('tahun', $request->tahun)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk kecamatan dan tahun ini sudah ada. Gunakan fitur edit untuk mengubah data.',
                ], 422);
            }

            $statistik = StatistikPenduduk::create([
                'statistik_penduduk_id' => Str::uuid()->toString(),
                'kecamatan_id' => $request->kecamatan_id,
                'tahun' => $request->tahun,
                'total_penduduk' => $request->total_penduduk,
            ]);

            Log::info('Admin: Statistik penduduk berhasil dibuat', [
                'id' => $statistik->statistik_penduduk_id,
                'kecamatan_id' => $request->kecamatan_id,
                'tahun' => $request->tahun,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik penduduk berhasil disimpan.',
                'data' => $statistik,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal menyimpan statistik penduduk', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
            ], 500);
        }
    }

    /**
     * Update the specified statistik penduduk.
     */
    public function update(StatistikPendudukRequest $request, string $id): JsonResponse
    {
        try {
            $statistik = StatistikPenduduk::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $exists = StatistikPenduduk::where('kecamatan_id', $request->kecamatan_id)
                ->where('tahun', $request->tahun)
                ->where('statistik_penduduk_id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik untuk kecamatan dan tahun ini sudah ada.',
                ], 422);
            }

            $statistik->update([
                'kecamatan_id' => $request->kecamatan_id,
                'tahun' => $request->tahun,
                'total_penduduk' => $request->total_penduduk,
            ]);

            Log::info('Admin: Statistik penduduk berhasil diupdate', [
                'id' => $statistik->statistik_penduduk_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik penduduk berhasil diperbarui.',
                'data' => $statistik->fresh(),
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal update statistik penduduk', [
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
     * Remove the specified statistik penduduk.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $statistik = StatistikPenduduk::find($id);

            if (!$statistik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data statistik tidak ditemukan.',
                ], 404);
            }

            $statistik->delete();

            Log::info('Admin: Statistik penduduk berhasil dihapus', [
                'id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data statistik penduduk berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Admin: Gagal hapus statistik penduduk', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }
}
