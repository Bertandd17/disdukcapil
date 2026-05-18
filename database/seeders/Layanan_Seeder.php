<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Layanan_Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Layanan_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('layanan')->delete();

        $data = [
            [
                'kode_layanan' => 'kk_perubahan',
                'nama_layanan' => 'Penerbitan Kartu Keluarga Karena Perubahan Data'
            ],
            [
                'kode_layanan' => 'akte_kelahiran',
                'nama_layanan' => 'Penerbitan Akte Kelahiran'
            ],
            [
                'kode_layanan' => 'akte_kematian',
                'nama_layanan' => 'Penerbitan Akte Kematian'
            ],
            [
                'kode_layanan' => 'lahir_mati',
                'nama_layanan' => 'Penerbitan Akte Lahir Mati'
            ],
            [
                'kode_layanan' => 'perkawinan',
                'nama_layanan' => 'Penerbitan Akte Perkawinan'
            ],
            [
                'kode_layanan' => 'kk_ganti_kepala',
                'nama_layanan' => 'Penerbitan KK Ganti Kepala Keluarga'
            ],
            [
                'kode_layanan' => 'kk_hilang',
                'nama_layanan' => 'Penerbitan KK Hilang/Rusak'
            ],
            [
                'kode_layanan' => 'kk_pisah',
                'nama_layanan' => 'Penerbitan KK Pisah'
            ],
        ];

        $insert = [];

        foreach ($data as $item) {
            $insert[] = [
                'layanan_id' => $item['kode_layanan'],
                'kode_layanan' => $item['kode_layanan'], 
                'nama_layanan' => $item['nama_layanan'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('layanan')->insert($insert);
    }
}
