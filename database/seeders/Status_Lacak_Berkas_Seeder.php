<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LacakBerkasModel;
use App\Models\AntrianOnlineModel;

class Status_Lacak_Berkas_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Status yang tersedia untuk lacak berkas
        $status_list = [
            'Dokumen Diterima',
            'Verifikasi Data',
            'Proses Cetak',
            'Siap Pengambilan',
            'Ditolak',
        ];

        // Untuk demo, kita akan membuat riwayat status untuk setiap antrian yang ada
        $antrian_list = AntrianOnlineModel::all();

        foreach ($antrian_list as $antrian) {
            $tanggal_awal = $antrian->tanggal;

            foreach ($status_list as $index => $status) {
                $tanggal = date('Y-m-d', strtotime($tanggal_awal . " +{$index} days"));

                LacakBerkasModel::create([
                    'antrian_online_id' => $antrian->antrian_online_id,
                    'status' => $status,
                    'tanggal' => $tanggal,
                    'keterangan' => "Status diperbarui: {$status}",
                ]);
            }
        }
    }
}
