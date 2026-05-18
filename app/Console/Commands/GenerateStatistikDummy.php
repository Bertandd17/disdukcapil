<?php

namespace App\Console\Commands;

use App\Models\Kecamatan;
use App\Models\StatistikPenduduk;
use App\Models\StatistikDokumen;
use App\Models\StatistikLayananBulanan;
use Illuminate\Console\Command;

class GenerateStatistikDummy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistik:generate-dummy {tahun?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate data statistik dummy untuk testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = $this->argument('tahun') ?? date('Y');

        $this->info("Generating statistik dummy untuk tahun {$tahun}...");

        $this->generateStatistikPenduduk($tahun);
        $this->generateStatistikDokumen($tahun);
        $this->generateStatistikLayanan($tahun);

        $this->info("✓ Data statistik dummy berhasil di-generate untuk tahun {$tahun}");

        return Command::SUCCESS;
    }

    /**
     * Generate statistik penduduk per kecamatan
     */
    private function generateStatistikPenduduk(int $tahun): void
    {
        $this->info("Generating statistik penduduk...");

        $kecamatans = Kecamatan::all();

        foreach ($kecamatans as $kecamatan) {
            StatistikPenduduk::updateOrCreate(
                [
                    'kecamatan_id' => $kecamatan->kecamatan_id,
                    'tahun' => $tahun,
                ],
                [
                    'total_penduduk' => rand(5000, 30000),
                ]
            );
        }

        $this->info("✓ Statistik penduduk untuk " . $kecamatans->count() . " kecamatan berhasil di-generate");
    }

    /**
     * Generate statistik dokumen bulanan
     */
    private function generateStatistikDokumen(int $tahun): void
    {
        $this->info("Generating statistik dokumen...");

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            StatistikDokumen::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'jumlah_kk' => rand(50, 200),
                    'jumlah_akte_lahir' => rand(30, 100),
                    'jumlah_akte_kematian' => rand(10, 50),
                    'jumlah_ktp' => rand(100, 300),
                    'jumlah_kia' => rand(20, 80),
                    'is_auto_generated' => false,
                    'generated_at' => now(),
                ]
            );
        }

        $this->info("✓ Statistik dokumen untuk 12 bulan berhasil di-generate");
    }

    /**
     * Generate statistik layanan bulanan
     */
    private function generateStatistikLayanan(int $tahun): void
    {
        $this->info("Generating statistik layanan...");

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $totalAntrian = rand(200, 500);
            $antrianSelesai = rand(150, 400);
            $antrianDiproses = rand(20, 50);
            $antrianMenunggu = $totalAntrian - $antrianSelesai - $antrianDiproses;

            StatistikLayananBulanan::updateOrCreate(
                [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'total_antrian' => $totalAntrian,
                    'antrian_selesai' => $antrianSelesai,
                    'antrian_diproses' => $antrianDiproses,
                    'antrian_menunggu' => max(0, $antrianMenunggu),
                    'antrian_ditolak' => rand(0, 20),
                    'waktu_avg_penanganan_menit' => rand(30, 120),
                    'persentase_kepuasan' => rand(70, 95),
                    'is_auto_generated' => false,
                    'generated_at' => now(),
                ]
            );
        }

        $this->info("✓ Statistik layanan untuk 12 bulan berhasil di-generate");
    }
}
