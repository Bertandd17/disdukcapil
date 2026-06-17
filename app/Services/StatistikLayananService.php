<?php

namespace App\Services;

use App\Models\StatistikLayananBulanan;
use App\Models\AntrianOnline;

class StatistikLayananService
{
    private const LAYANAN_KEYWORDS = [
        'jumlah_kk' => ['Kartu Keluarga', 'KK'],
        'jumlah_kelahiran' => ['Akta Kelahiran', 'Akte Lahir', 'Akte Kelahiran', 'Kelahiran'],
        'jumlah_kematian' => ['Akta Kematian', 'Akte Kematian', 'Kematian'],
        'jumlah_lahir_mati' => ['Lahir Mati'],
        'jumlah_pernikahan' => ['Pernikahan'],
    ];

    public function generateDariAntrian(int $tahun, int $bulan): StatistikLayananBulanan
    {
        $antrianQuery = AntrianOnline::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan);

        $antrianSelesai = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_SIAP_PENGAMBILAN)->count();
        $antrianDiproses = (clone $antrianQuery)->whereIn('status_antrian', [
            AntrianOnline::STATUS_DOKUMEN_DITERIMA,
            AntrianOnline::STATUS_VERIFIKASI,
            AntrianOnline::STATUS_PROSES_CETAK,
        ])->count();
        $antrianMenunggu = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_MENUNGGU)->count();
        $antrianDitolak = (clone $antrianQuery)->where('status_antrian', AntrianOnline::STATUS_DITOLAK)->count();

        $layananCounts = [];
        foreach (self::LAYANAN_KEYWORDS as $field => $keywords) {
            $layananCounts[$field] = $this->hitungBerdasarkanNamaLayanan($tahun, $bulan, $keywords);
        }

        $totalAntrian = array_sum($layananCounts);
        if ($totalAntrian === 0) {
            $totalAntrian = $antrianQuery->count();
        }

        $rataRataWaktu = $this->hitungRataRataWaktuSelesai($tahun, $bulan);
        $tingkatKeberhasilan = $totalAntrian > 0
            ? round(($antrianSelesai / $totalAntrian) * 100, 2)
            : 0;

        $statistik = StatistikLayananBulanan::withTrashed()->updateOrCreate(
            [
                'tahun' => $tahun,
                'bulan' => $bulan,
            ],
            array_merge($layananCounts, [
                'total_antrian' => $totalAntrian,
                'antrian_selesai' => $antrianSelesai,
                'antrian_diproses' => $antrianDiproses,
                'antrian_menunggu' => $antrianMenunggu,
                'antrian_ditolak' => $antrianDitolak,
                'waktu_avg_penanganan_menit' => $rataRataWaktu,
                'persentase_kepuasan' => $tingkatKeberhasilan,
                'is_auto_generated' => true,
                'generated_at' => now(),
            ])
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
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();
    }

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
            $totalMenit += $antrian->updated_at->diffInMinutes($antrian->created_at);
            $count++;
        }

        return $count > 0 ? (int) round($totalMenit / $count) : 0;
    }

    public function generateBulanBerjalan(): StatistikLayananBulanan
    {
        $now = now();
        return $this->generateDariAntrian(
            (int) $now->format('Y'),
            (int) $now->format('m')
        );
    }

    public function generateBulanLalu(): ?StatistikLayananBulanan
    {
        $lastMonth = now()->subMonth();
        $tahun = (int) $lastMonth->format('Y');
        $bulan = (int) $lastMonth->format('m');

        $existing = StatistikLayananBulanan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if ($existing) {
            return null;
        }

        return $this->generateDariAntrian($tahun, $bulan);
    }

    public function regenerateBulanan(int $tahun, int $bulan): StatistikLayananBulanan
    {
        StatistikLayananBulanan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->delete();

        return $this->generateDariAntrian($tahun, $bulan);
    }

    public function generateRangeBulan(int $tahun, int $bulanAwal, int $bulanAkhir): array
    {
        $results = [];

        for ($bulan = $bulanAwal; $bulan <= $bulanAkhir; $bulan++) {
            $results[] = $this->generateDariAntrian($tahun, $bulan);
        }

        return $results;
    }

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

    public function generateTahun(int $tahun): array
    {
        return $this->generateRangeBulan($tahun, 1, 12);
    }

    public function generateTahunan(int $tahun): array
    {
        return [
            'success' => true,
            'message' => 'Statistik layanan berhasil digenerate untuk satu tahun penuh.',
            'data' => $this->generateTahun($tahun),
        ];
    }

    public function getRingkasan(int $tahun): array
    {
        $statistik = StatistikLayananBulanan::where('tahun', $tahun)->get();

        return [
            'total_antrian' => $statistik->sum('total_antrian'),
            'total_kk' => $statistik->sum('jumlah_kk'),
            'total_kelahiran' => $statistik->sum('jumlah_kelahiran'),
            'total_kematian' => $statistik->sum('jumlah_kematian'),
            'total_lahir_mati' => $statistik->sum('jumlah_lahir_mati'),
            'total_pernikahan' => $statistik->sum('jumlah_pernikahan'),
            'total_selesai' => $statistik->sum('antrian_selesai'),
            'total_diproses' => $statistik->sum('antrian_diproses'),
            'total_menunggu' => $statistik->sum('antrian_menunggu'),
            'total_ditolak' => $statistik->sum('antrian_ditolak'),
            'rata_rata_waktu' => round($statistik->avg('waktu_avg_penanganan_menit'), 2),
            'rata_rata_kepuasan' => round($statistik->avg('persentase_kepuasan'), 2),
            'bulan_count' => $statistik->count(),
        ];
    }

    public function getTrenBulanan(int $tahun): array
    {
        $statistik = StatistikLayananBulanan::where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        return $statistik->map(fn ($item) => [
            'bulan' => $item->bulan,
            'nama_bulan' => $item->nama_bulan,
            'total_antrian' => $item->total_antrian,
            'jumlah_kk' => $item->jumlah_kk ?? 0,
            'jumlah_kelahiran' => $item->jumlah_kelahiran ?? 0,
            'jumlah_kematian' => $item->jumlah_kematian ?? 0,
            'jumlah_lahir_mati' => $item->jumlah_lahir_mati ?? 0,
            'jumlah_pernikahan' => $item->jumlah_pernikahan ?? 0,
        ])->toArray();
    }
}
