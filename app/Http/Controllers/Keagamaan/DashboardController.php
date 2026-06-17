<?php

namespace App\Http\Controllers\Keagamaan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Keagamaan\Concerns\ResolvesKeagamaanAccount;
use App\Models\LayananPernikahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ResolvesKeagamaanAccount;

    /**
     * Tampilkan dashboard keagamaan — data difilter tempat ibadah login.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $page_title = 'Dashboard Keagamaan';
        $keagamaan = $this->resolveKeagamaanAccount();
        $keagamaanId = $keagamaan->keagamaan_id;

        $statistics = [
            'total' => LayananPernikahan::forKeagamaanId($keagamaanId)->count(),
            'pending' => LayananPernikahan::forKeagamaanId($keagamaanId)
                ->menungguKonfirmasiKeagamaan()->count(),
            'proses' => LayananPernikahan::forKeagamaanId($keagamaanId)
                ->whereIn('status', [
                    LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                    LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                    LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                ])->count(),
            'selesai' => LayananPernikahan::forKeagamaanId($keagamaanId)
                ->where('status', LayananPernikahan::STATUS_SELESAI)->count(),
        ];

        $recentPernikahan = LayananPernikahan::forKeagamaanId($keagamaanId)
            ->whereIn('status', [
                LayananPernikahan::STATUS_MENUNGGU_KONFIRMASI_KEAGAMAAN,
                LayananPernikahan::STATUS_MENUNGGU_APPROVE_TANGGAL,
                LayananPernikahan::STATUS_TANGGAL_DISETUJUI,
                LayananPernikahan::STATUS_DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI,
                LayananPernikahan::STATUS_DOKUMEN_PERLU_PERBAIKAN,
                LayananPernikahan::STATUS_DOKUMEN_DIVERIFIKASI,
                LayananPernikahan::STATUS_SELESAI,
            ])
            ->urutTanggalPermintaan()
            ->limit(5)
            ->get();

        return view('keagamaan.dashboard', compact(
            'user',
            'page_title',
            'keagamaan',
            'statistics',
            'recentPernikahan'
        ));
    }
}
