<?php

namespace Database\Seeders;

use App\Models\Jenis_Keagamaan_Model;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisKeagamaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $katolikLama = Jenis_Keagamaan_Model::where('nama_jenis_keagamaan', 'Katolik')->first();
        if ($katolikLama && ! Jenis_Keagamaan_Model::where('nama_jenis_keagamaan', 'Kristen Katolik')->exists()) {
            $katolikLama->update(['nama_jenis_keagamaan' => 'Kristen Katolik']);
        }

        $agama = [
            'Islam',
            'Kristen Protestan',
            'Kristen Katolik',
            'Hindu',
            'Buddha',
            'Konghucu',
        ];

        foreach ($agama as $nama) {
            Jenis_Keagamaan_Model::firstOrCreate([
                'nama_jenis_keagamaan' => $nama,
            ]);
        }
    }
}
