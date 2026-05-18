<?php

namespace App\Services;

use App\Models\StatistikLayananBulanan;
use App\Models\AntrianOnline;
use Illuminate\Support\Facades\DB;

class StatistikLayananService
{
    /**
     * Generate statistik layanan bulanan dari data antrian
     *
     * @param int $tahun
     * @param int $bulan
     * @return StatistikLayananBulanan
     */
    public function generateDariAntrian(int $tahun, int $bulan): StatistikLayananBulanan
    {
        // Ambil semua antrian pada bulan/tahun tertentu
        $antrianQuery = AntrianOnline::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan);

        // Hitung statistik antrian
        $totalAntrian = $antrianQuery->count();
        $antrianSelesai = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_SIAP_PENGAMBILAN)->count();
        $antrianDiproses = (clone $antrianQuery)->whereIn('status_antrian', [
            AntrianOnline::STATUS_DOKUMEN_DITERIMA,
            AntrianOnline::STATUS_VERIFIKASI,
            AntrianOnline::STATUS_PROSES_CETAK,
        ])->count();
        $antrianMenunggu = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_MENUNGGU)->count();
        $antrianDitolak = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_DITOLAK)->count();

        // Hitung rata-rata waktu penyelesaian (dalam menit)
        $rataRataWaktu = $this->hitungRataRataWaktuSelesai($tahun, $bulan);

        // Hitung tingkat keberhasilan (persentase antrian selesai)
        $tingkatKeberhasilan = $totalAntrian > 0 
            ? round(($antrianSelesai / $totalAntrian) * 100, 2) 
            : 0;

        // Update or create statistik
        $statistik = StatistikLayananBulanan::withTrashed()->updateOrCreate(
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            [
                'total_antrian' => $totalAntrian,
                'antrian_selesai' => $antrianSelesai,
                'antrian_diproses' => $antrianDiproses,
                'antrian_menunggu' => $antrianMenunggu,
                'antrian_ditolak' => $antrianDitolak,
                'waktu_avg_penanganan_menit' => $rataRataWaktu,
                'persentase_kepuasan' => $tingkatKeberhasilan,
                'is_auto_generated' => true,
                'generated_at' => now(),
            ]
        );

        if ($statistik->trashed()) {
            $statistik->restore();
        }

        return $statistik;
    }

    /**
     * Hitung rata-rata waktu penyelesaian (dalam menit)
     */
    protected function hitungRataRataWaktuSelesai(int $tahun, int $bulan): int
    {
        $antrianSelesai = AntrianOnline::where('status_antrian', AntrianOnline::STATUS_SIAP_PENGAMBILAN)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($antrianSelesai->isEmpty()) {
            return 0;
        }

        $totalMenit = 0;
        $count = 0;

        foreach ($antrianSelesai as $antrian) {
            $selisih = $antrian->updated_at->diffInMinutes($antrian->created_at);
            $totalMenit += $selisih;
            $count++;
        }

        return $count > 0 ? (int) round($totalMenit / $count) : 0;
    }

    /**
     * Generate statistik untuk bulan berjalan (current month)
     */
    public function generateBulanBerjalan(): StatistikLayananBulanan
    {
        $now = now();
        return $this->generateDariAntrian(
            (int) $now->format('Y'),
            (int) $now->format('m')
        );
    }

    /**
     * Generate statistik untuk bulan sebelumnya (jika belum ada)
     */
    public function generateBulanLalu(): ?StatistikLayananBulanan
    {
        $lastMonth = now()->subMonth();
        $tahun = (int) $lastMonth->format('Y');
        $bulan = (int) $lastMonth->format('m');

        // Cek apakah sudah ada
        $existing = StatistikLayananBulanan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if ($existing) {
            return null; // Sudah ada, tidak perlu generate ulang
        }

        return $this->generateDariAntrian($tahun, $bulan);
    }

    /**
     * Re-generate statistik (update data yang sudah ada)
     */
    public function regenerateBulanan(int $tahun, int $bulan): StatistikLayananBulanan
    {
        // Hapus data yang sudah ada
        StatistikLayananBulanan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->delete();

        // Generate ulang
        return $this->generateDariAntrian($tahun, $bulan);
    }

    /**
     * Generate statistik untuk range bulan tertentu
     *
     * @param int $tahun
     * @param int $bulanAwal
     * @param int $bulanAkhir
     * @return array
     */
    public function generateRangeBulan(int $tahun, int $bulanAwal, int $bulanAkhir): array
    {
        $results = [];

        for ($bulan = $bulanAwal; $bulan <= $bulanAkhir; $bulan++) {
            $results[] = $this->generateDariAntrian($tahun, $bulan);
        }

        return $results;
    }

    /**
     * Generate statistik untuk rentang bulan dan kembalikan response standar controller.
     */
    public function generateRange(int $tahun, int $bulanAwal, int $bulanAkhir): array
    {
        if ($bulanAwal > $bulanAkhir) {
            return [
                'success' => false,
                'message' => 'Bulan awal tidak boleh lebih besar dari bulan akhir.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Statistik layanan berhasil digenerate untuk rentang bulan yang dipilih.',
            'data' => $this->generateRangeBulan($tahun, $bulanAwal, $bulanAkhir),
        ];
    }

    /**
     * Generate semua statistik untuk tahun tertentu
     *
     * @param int $tahun
     * @return array
     */
    public function generateTahun(int $tahun): array
    {
        return $this->generateRangeBulan($tahun, 1, 12);
    }

    /**
     * Generate statistik untuk satu tahun penuh dan kembalikan response standar controller.
     */
    public function generateTahunan(int $tahun): array
    {
        return [
            'success' => true,
            'message' => 'Statistik layanan berhasil digenerate untuk satu tahun penuh.',
            'data' => $this->generateTahun($tahun),
        ];
    }

    /**
     * Get ringkasan statistik layanan untuk tahun tertentu
     */
    public function getRingkasan(int $tahun): array
    {
        $statistik = StatistikLayananBulanan::where('tahun', $tahun)->get();

        return [
            'total_antrian' => $statistik->sum('total_antrian'),
            'total_selesai' => $statistik->sum('antrian_selesai'),
            'total_diproses' => $statistik->sum('antrian_diproses'),
            'total_menunggu' => $statistik->sum('antrian_menunggu'),
            'total_ditolak' => $statistik->sum('antrian_ditolak'),
            'rata_rata_waktu' => round($statistik->avg('waktu_avg_penanganan_menit'), 2),
            'rata_rata_kepuasan' => round($statistik->avg('persentase_kepuasan'), 2),
            'bulan_count' => $statistik->count(),
        ];
    }

    /**
     * Get tren statistik layanan bulanan untuk chart
     */
    public function getTrenBulanan(int $tahun): array
    {
        $statistik = StatistikLayananBulanan::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        return $statistik->map(fn($item) => [
            'bulan' => $item->bulan,
            'nama_bulan' => $item->nama_bulan,
            'total_antrian' => $item->total_antrian,
            'antrian_selesai' => $item->antrian_selesai,
            'antrian_diproses' => $item->antrian_diproses,
            'antrian_menunggu' => $item->antrian_menunggu,
        ])->toArray();
    }
}
