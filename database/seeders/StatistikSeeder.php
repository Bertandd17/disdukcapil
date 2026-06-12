<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\StatistikPenduduk;
use App\Models\StatistikDokumen;
use App\Models\StatistikLayananBulanan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatistikSeeder extends Seeder
{
    /**
     * Data statistik penduduk per kecamatan (realistis untuk Kabupaten Toba)
     */
    private array $dataPenduduk = [
        ['Balige', 2024, 45234],
        ['Balige', 2023, 44120],
        ['Tampahan', 2024, 28456],
        ['Tampahan', 2023, 27890],
        ['Laguboti', 2024, 35678],
        ['Laguboti', 2023, 34520],
        ['Habinsaran', 2024, 22567],
        ['Habinsaran', 2023, 21890],
        ['Pintu Pohan Meranti', 2024, 19432],
        ['Pintu Pohan Meranti', 2023, 18765],
        ['Siantar Narumonda', 2024, 17890],
        ['Siantar Narumonda', 2023, 17234],
        ['Porsea', 2024, 38901],
        ['Porsea', 2023, 37543],
        ['Bor-bor', 2024, 24567],
        ['Bor-bor', 2023, 23890],
        ['Nalela', 2024, 15678],
        ['Nalela', 2023, 15234],
        ['Uluan', 2024, 14345],
        ['Uluan', 2023, 13987],
        ['Ajibata', 2024, 12890],
        ['Ajibata', 2023, 12543],
        ['Damit', 2024, 11234],
        ['Damit', 2023, 10987],
        ['Lumban Julu', 2024, 16543],
        ['Lumban Julu', 2023, 16098],
        ['Sigumpar', 2024, 13456],
        ['Sigumpar', 2023, 13123],
        ['Nassau', 2024, 9876],
        ['Nassau', 2023, 9654],
        ['Silaen', 2024, 21345],
        ['Silaen', 2023, 20789],
    ];

    /**
     * Data statistik dokumen per bulan (realistis)
     */
    private array $dataDokumen = [
        // 2024
        [2024, 1, 456, 234, 89, 567, 345],
        [2024, 2, 423, 198, 76, 534, 312],
        [2024, 3, 489, 267, 92, 589, 378],
        [2024, 4, 445, 223, 84, 523, 356],
        [2024, 5, 467, 245, 88, 545, 367],
        [2024, 6, 478, 256, 91, 556, 372],
        [2024, 7, 491, 278, 95, 578, 389],
        [2024, 8, 434, 212, 82, 514, 334],
        [2024, 9, 456, 234, 87, 536, 345],
        [2024, 10, 468, 247, 89, 548, 356],
        [2024, 11, 482, 259, 93, 562, 368],
        [2024, 12, 495, 268, 96, 585, 378],
        // 2023
        [2023, 1, 412, 201, 78, 501, 298],
        [2023, 2, 378, 178, 65, 467, 267],
        [2023, 3, 445, 223, 81, 534, 312],
        [2023, 4, 401, 189, 72, 489, 289],
        [2023, 5, 423, 198, 76, 501, 301],
        [2023, 6, 434, 212, 79, 512, 312],
        [2023, 7, 456, 234, 85, 534, 334],
        [2023, 8, 389, 167, 68, 456, 278],
        [2023, 9, 412, 189, 74, 478, 298],
        [2023, 10, 423, 198, 77, 489, 301],
        [2023, 11, 434, 209, 81, 501, 312],
        [2023, 12, 445, 215, 84, 512, 323],
    ];

    /**
     * Data statistik layanan bulanan (antrian)
     */
    private array $dataLayanan = [
        // 2024
        [2024, 1, 1234, 145, 345, 689, 55, 28, 87.5],
        [2024, 2, 1156, 123, 312, 656, 65, 27, 86.8],
        [2024, 3, 1345, 167, 378, 723, 77, 26, 88.2],
        [2024, 4, 1223, 134, 334, 689, 66, 27, 87.1],
        [2024, 5, 1289, 145, 356, 712, 76, 26, 88.5],
        [2024, 6, 1312, 156, 367, 723, 66, 25, 87.8],
        [2024, 7, 1378, 178, 389, 745, 66, 24, 89.2],
        [2024, 8, 1167, 112, 312, 689, 54, 28, 86.5],
        [2024, 9, 1234, 134, 345, 689, 66, 27, 87.3],
        [2024, 10, 1298, 145, 356, 734, 63, 26, 88.1],
        [2024, 11, 1356, 167, 378, 745, 66, 25, 88.7],
        [2024, 12, 1412, 189, 389, 768, 66, 24, 89.5],
        // 2023
        [2023, 1, 1089, 98, 289, 645, 57, 29, 85.2],
        [2023, 2, 1023, 78, 256, 612, 77, 31, 84.8],
        [2023, 3, 1178, 123, 312, 689, 54, 28, 86.5],
        [2023, 4, 1056, 89, 278, 634, 55, 30, 85.1],
        [2023, 5, 1123, 98, 289, 656, 80, 29, 85.8],
        [2023, 6, 1145, 109, 301, 667, 68, 28, 86.2],
        [2023, 7, 1234, 134, 345, 689, 66, 27, 87.5],
        [2023, 8, 1067, 67, 234, 612, 154, 32, 83.4],
        [2023, 9, 1089, 89, 278, 634, 88, 30, 84.9],
        [2023, 10, 1123, 98, 289, 645, 91, 29, 85.3],
        [2023, 11, 1178, 112, 312, 667, 87, 28, 86.1],
        [2023, 12, 1234, 134, 334, 689, 77, 27, 86.8],
    ];

    public function run(): void
    {
        $this->command->info('Memulai seeder data statistik...');

        // Disable query log untuk performance
        DB::disableQueryLog();

        // Seed Statistik Penduduk
        $this->seedStatistikPenduduk();

        // Seed Statistik Dokumen
        $this->seedStatistikDokumen();

        // Seed Statistik Layanan Bulanan
        $this->seedStatistikLayananBulanan();

        DB::enableQueryLog();

        $this->command->newLine();
        $this->command->info(' Seeder data statistik selesai!');
    }

    private function seedStatistikPenduduk(): void
    {
        $this->command->info(' Menyimpan data statistik penduduk...');

        // Ambil semua kecamatan
        $kecamatanList = Kecamatan::all()->keyBy('nama_kecamatan');

        $saved = 0;
        foreach ($this->dataPenduduk as $data) {
            [$namaKecamatan, $tahun, $totalPenduduk] = $data;

            $kecamatan = $kecamatanList->get($namaKecamatan);
            if (!$kecamatan) {
                continue;
            }

            $statistik = StatistikPenduduk::withTrashed()->updateOrCreate(
                [
                    'kecamatan_id' => $kecamatan->kecamatan_id,
                    'tahun' => $tahun,
                ],
                [
                    'total_penduduk' => $totalPenduduk,
                ]
            );

            if ($statistik->trashed()) {
                $statistik->restore();
            }

            $saved++;
        }

        $this->command->info("   {$saved} data statistik penduduk berhasil disimpan");
    }

    private function seedStatistikDokumen(): void
    {
        $this->command->info(' Menyimpan data statistik dokumen...');

        $saved = 0;
        foreach ($this->dataDokumen as $data) {
            [$tahun, $bulan, $kk, $akteLahir, $akteKematian, $ktp, $kia] = $data;

            $statistik = StatistikDokumen::withTrashed()->updateOrCreate(
                [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'jumlah_kk' => $kk,
                    'jumlah_akte_lahir' => $akteLahir,
                    'jumlah_akte_kematian' => $akteKematian,
                    'jumlah_ktp' => $ktp,
                    'jumlah_kia' => $kia,
                    'is_auto_generated' => false,
                    'generated_at' => null,
                ]
            );

            if ($statistik->trashed()) {
                $statistik->restore();
            }

            $saved++;
        }

        $this->command->info("   {$saved} data statistik dokumen berhasil disimpan");
    }

    private function seedStatistikLayananBulanan(): void
    {
        $this->command->info(' Menyimpan data statistik layanan bulanan...');

        $saved = 0;
        foreach ($this->dataLayanan as $data) {
            [
                $tahun,
                $bulan,
                $total,
                $menunggu,
                $diproses,
                $selesai,
                $ditolak,
                $waktuAvg,
                $kepuasan
            ] = $data;

            $statistik = StatistikLayananBulanan::withTrashed()->updateOrCreate(
                [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'total_antrian' => $total,
                    'antrian_menunggu' => $menunggu,
                    'antrian_diproses' => $diproses,
                    'antrian_selesai' => $selesai,
                    'antrian_ditolak' => $ditolak,
                    'waktu_avg_penanganan_menit' => $waktuAvg,
                    'persentase_kepuasan' => $kepuasan,
                    'is_auto_generated' => false,
                    'generated_at' => null,
                ]
            );

            if ($statistik->trashed()) {
                $statistik->restore();
            }

            $saved++;
        }

        $this->command->info("   {$saved} data statistik layanan bulanan berhasil disimpan");
    }
}
