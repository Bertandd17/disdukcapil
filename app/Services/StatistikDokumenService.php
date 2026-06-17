<?php

namespace App\Services;

use App\Models\StatistikDokumen;
use App\Models\AntrianOnline;

class StatistikDokumenService
{
    private const LAYANAN_KEYWORDS = [
        'jumlah_kk' => ['Kartu Keluarga', 'KK'],
        'jumlah_akte_lahir' => ['Akta Kelahiran', 'Akte Lahir', 'Akte Kelahiran', 'Kelahiran'],
        'jumlah_akte_kematian' => ['Akta Kematian', 'Akte Kematian', 'Kematian'],
        'jumlah_lahir_mati' => ['Lahir Mati'],
        'jumlah_pernikahan' => ['Pernikahan'],
    ];

    public function generateBulanan(int $tahun, int $bulan): StatistikDokumen
    {
        $payload = ['is_auto_generated' => true, 'generated_at' => now()];
        foreach (self::LAYANAN_KEYWORDS as $field => $keywords) {
            $payload[$field] = $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, $keywords);
        }

        $statistik = StatistikDokumen::withTrashed()->updateOrCreate(
            ['tahun' => $tahun, 'bulan' => $bulan],
            $payload
        );

        if ($statistik->trashed()) {
            $statistik->restore();
        }

        return $statistik;
    }

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
            ->whereIn('status_antrian', [
                AntrianOnline::STATUS_SIAP_PENGAMBILAN,
                'Selesai',
            ])
            ->count();
    }

    public function generateBulanBerjalan(): StatistikDokumen
    {
        $now = now();
        return $this->generateBulanan((int) $now->format('Y'), (int) $now->format('m'));
    }

    public function regenerateBulanan(int $tahun, int $bulan): StatistikDokumen
    {
        StatistikDokumen::where('tahun', $tahun)->where('bulan', $bulan)->delete();
        return $this->generateBulanan($tahun, $bulan);
    }

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

    public function generateTahunan(int $tahun): array
    {
        return $this->generateRange($tahun, 1, 12);
    }

    public function getRingkasan(int $tahun): array
    {
        $statistik = StatistikDokumen::where('tahun', $tahun)->get();

        return [
            'total_dokumen' => $statistik->sum('total_dokumen'),
            'total_kk' => $statistik->sum('jumlah_kk'),
            'total_akte_lahir' => $statistik->sum('jumlah_akte_lahir'),
            'total_akte_kematian' => $statistik->sum('jumlah_akte_kematian'),
            'total_lahir_mati' => $statistik->sum('jumlah_lahir_mati'),
            'total_pernikahan' => $statistik->sum('jumlah_pernikahan'),
            'bulan_count' => $statistik->count(),
        ];
    }
}
