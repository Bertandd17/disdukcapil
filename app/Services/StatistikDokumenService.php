<?php

namespace App\Services;

use App\Models\StatistikDokumen;
use App\Models\AntrianOnline;
use Illuminate\Support\Facades\DB;

class StatistikDokumenService
{
    /**
     * Generate statistik dokumen bulanan dari data transaksi
     *
     * @param int $tahun
     * @param int $bulan
     * @return StatistikDokumen
     */
    public function generateBulanan(int $tahun, int $bulan): StatistikDokumen
    {
        // Hitung jumlah dokumen berdasarkan bulan dan tahun
        $jumlahKk = $this->hitungKK($tahun, $bulan);
        $jumlahAkteLahir = $this->hitungAkteLahir($tahun, $bulan);
        $jumlahAkteKematian = $this->hitungAkteKematian($tahun, $bulan);
        $jumlahKtp = $this->hitungKTP($tahun, $bulan);
        $jumlahKia = $this->hitungKIA($tahun, $bulan);

        // Update or create statistik
        $statistik = StatistikDokumen::withTrashed()->updateOrCreate(
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            [
                'jumlah_kk' => $jumlahKk,
                'jumlah_akte_lahir' => $jumlahAkteLahir,
                'jumlah_akte_kematian' => $jumlahAkteKematian,
                'jumlah_ktp' => $jumlahKtp,
                'jumlah_kia' => $jumlahKia,
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
     * Hitung jumlah KK yang diterbitkan pada bulan/tahun tertentu
     */
    protected function hitungKK(int $tahun, int $bulan): int
    {
        return $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, ['Kartu Keluarga', 'KK']);
    }

    /**
     * Hitung jumlah Akte Lahir yang diterbitkan
     */
    protected function hitungAkteLahir(int $tahun, int $bulan): int
    {
        return $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, ['Akta Kelahiran', 'Akte Lahir', 'Akte Kelahiran']);
    }

    /**
     * Hitung jumlah Akte Kematian yang diterbitkan
     */
    protected function hitungAkteKematian(int $tahun, int $bulan): int
    {
        return $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, ['Akta Kematian', 'Akte Kematian']);
    }

    /**
     * Hitung jumlah KTP yang diterbitkan
     */
    protected function hitungKTP(int $tahun, int $bulan): int
    {
        return $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, ['KTP']);
    }

    /**
     * Hitung jumlah KIA yang diterbitkan
     */
    protected function hitungKIA(int $tahun, int $bulan): int
    {
        return $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, ['KIA']);
    }

    /**
     * Hitung jumlah antrian selesai berdasarkan nama layanan.
     */
    protected function hitungBerdasarkanNamaLayanan(int $tahun, int $bulan, array $keywords): int
    {
        return AntrianOnline::whereHas('layanan', function ($query) use ($keywords) {
                $query->where(function ($subQuery) use ($keywords) {
                    foreach ($keywords as $index => $keyword) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $subQuery->{$method}('nama_layanan', 'like', "%{$keyword}%");
                    }
                });
            })
            ->whereYear('updated_at', $tahun)
            ->whereMonth('updated_at', $bulan)
            ->where('status_antrian', AntrianOnline::STATUS_SIAP_PENGAMBILAN)
            ->count();
    }

    /**
     * Generate statistik untuk bulan berjalan (current month)
     */
    public function generateBulanBerjalan(): StatistikDokumen
    {
        $now = now();
        return $this->generateBulanan(
            (int) $now->format('Y'),
            (int) $now->format('m')
        );
    }

    /**
     * Re-generate statistik (update data yang sudah ada)
     */
    public function regenerateBulanan(int $tahun, int $bulan): StatistikDokumen
    {
        // Hapus data yang sudah ada
        StatistikDokumen::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->delete();

        // Generate ulang
        return $this->generateBulanan($tahun, $bulan);
    }

    /**
     * Generate statistik untuk rentang bulan.
     */
    public function generateRange(int $tahun, int $bulanAwal, int $bulanAkhir): array
    {
        if ($bulanAwal > $bulanAkhir) {
            return [
                'success' => false,
                'message' => 'Bulan awal tidak boleh lebih besar dari bulan akhir.',
            ];
        }

        $data = [];
        for ($bulan = $bulanAwal; $bulan <= $bulanAkhir; $bulan++) {
            $data[] = $this->generateBulanan($tahun, $bulan);
        }

        return [
            'success' => true,
            'message' => 'Statistik dokumen berhasil digenerate untuk rentang bulan yang dipilih.',
            'data' => $data,
        ];
    }

    /**
     * Generate statistik untuk satu tahun penuh.
     */
    public function generateTahunan(int $tahun): array
    {
        return $this->generateRange($tahun, 1, 12);
    }

    /**
     * Get ringkasan statistik dokumen untuk tahun tertentu
     */
    public function getRingkasan(int $tahun): array
    {
        $statistik = StatistikDokumen::where('tahun', $tahun)->get();

        return [
            'total_dokumen' => $statistik->sum('total_dokumen'),
            'total_kk' => $statistik->sum('jumlah_kk'),
            'total_akte_lahir' => $statistik->sum('jumlah_akte_lahir'),
            'total_akte_kematian' => $statistik->sum('jumlah_akte_kematian'),
            'total_ktp' => $statistik->sum('jumlah_ktp'),
            'total_kia' => $statistik->sum('jumlah_kia'),
            'total_pernikahan' => $statistik->sum('jumlah_pernikahan'),
            'bulan_count' => $statistik->count(),
        ];
    }
}
