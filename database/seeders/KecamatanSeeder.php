<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KecamatanSeeder extends Seeder
{
    /**
     * Data 16 Kecamatan Kabupaten Toba
     */
    public function run(): void
    {
        $kecamatan = [
            // Kecamatan di Kabupaten Toba
            ['kode' => '1206010', 'nama' => 'Balige'],
            ['kode' => '1206020', 'nama' => 'Tampahan'],
            ['kode' => '1206030', 'nama' => 'Laguboti'],
            ['kode' => '1206040', 'nama' => 'Habinsaran'],
            ['kode' => '1206050', 'nama' => 'Pintu Pohan Meranti'],
            ['kode' => '1206060', 'nama' => 'Siantar Narumonda'],
            ['kode' => '1206070', 'nama' => 'Porsea'],
            ['kode' => '1206080', 'nama' => 'Bor-bor'],
            ['kode' => '1206090', 'nama' => 'Nalela'],
            ['kode' => '1206100', 'nama' => 'Uluan'],
            ['kode' => '1206110', 'nama' => 'Ajibata'],
            ['kode' => '1206120', 'nama' => 'Damit'],
            ['kode' => '1206130', 'nama' => 'Lumban Julu'],
            ['kode' => '1206140', 'nama' => 'Sigumpar'],
            ['kode' => '1206150', 'nama' => ' Nassau'],
            ['kode' => '1206160', 'nama' => 'Silaen'],
        ];

        foreach ($kecamatan as $kec) {
            DB::table('kecamatan')->insert([
                'kecamatan_id' => Str::uuid()->toString(),
                'kode_kecamatan' => $kec['kode'],
                'nama_kecamatan' => trim($kec['nama']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeder Kecamatan: 16 kecamatan berhasil di-seed.');
    }
}
