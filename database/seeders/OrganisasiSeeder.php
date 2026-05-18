<?php

namespace Database\Seeders;

use App\Models\Organisasi_Model;
use Illuminate\Database\Seeder;

class OrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data existing
        Organisasi_Model::query()->delete();

        $data = [
            // ========================================
            // PIMPINAN UTAMA
            // ========================================
            [
                'kode_posisi' => 'kadis',
                'nama_jabatan' => 'Kepala Dinas',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'pimpinan_utama',
                'eselon' => 'II.b',
                'urutan' => 1
            ],
            [
                'kode_posisi' => 'sekdin',
                'nama_jabatan' => 'Sekretaris',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'pimpinan_utama',
                'eselon' => 'III.d',
                'urutan' => 2
            ],

            // ========================================
            // BAGIAN DI BAWAH SEKRETARIS
            // ========================================
            [
                'kode_posisi' => 'umum_kepegawaian',
                'nama_jabatan' => 'Sub Bagian Umum dan Kepegawaian',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'sub_bagian',
                'eselon' => 'IV.a',
                'urutan' => 3
            ],
            [
                'kode_posisi' => 'kf_sekretariat_1',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 4
            ],
            [
                'kode_posisi' => 'perencanaan',
                'nama_jabatan' => 'Sub Bagian Perencanaan',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'sub_bagian',
                'eselon' => 'IV.a',
                'urutan' => 5
            ],
            [
                'kode_posisi' => 'koord_sekretariat',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 6
            ],
            [
                'kode_posisi' => 'kf_sekretariat_2',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 7
            ],

            // ========================================
            // BIDANG PELAYANAN PENDAFTARAN PENDUDUK
            // ========================================
            [
                'kode_posisi' => 'bidafduk',
                'nama_jabatan' => 'Bidang Pelayanan Pendaftaran Penduduk',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'bidang',
                'eselon' => 'III.c',
                'urutan' => 8
            ],
            [
                'kode_posisi' => 'koord_dafduk_1',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 9
            ],
            [
                'kode_posisi' => 'koord_dafduk_2',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 10
            ],
            [
                'kode_posisi' => 'kf_dafduk',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 11
            ],

            // ========================================
            // BIDANG PELAYANAN PENCATATAN SIPIL
            // ========================================
            [
                'kode_posisi' => 'bidapencatatan',
                'nama_jabatan' => 'Bidang Pelayanan Pencatatan Sipil',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'bidang',
                'eselon' => 'III.c',
                'urutan' => 12
            ],
            [
                'kode_posisi' => 'koord_pencatatan_1',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 13
            ],
            [
                'kode_posisi' => 'koord_pencatatan_2',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 14
            ],
            [
                'kode_posisi' => 'kf_pencatatan',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 15
            ],

            // ========================================
            // BIDANG PENGELOLAAN INFORMASI ADM. KEPENDUDUKAN
            // ========================================
            [
                'kode_posisi' => 'bid_informasi',
                'nama_jabatan' => 'Bidang Pengelolaan Informasi Adm. Kependudukan',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'bidang',
                'eselon' => 'III.c',
                'urutan' => 16
            ],
            [
                'kode_posisi' => 'koord_informasi_1',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 17
            ],
            [
                'kode_posisi' => 'koord_informasi_2',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 18
            ],
            [
                'kode_posisi' => 'kf_informasi',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 19
            ],

            // ========================================
            // BIDANG PEMANFAATAN DATA DAN INOVASI PELAYANAN
            // ========================================
            [
                'kode_posisi' => 'bid_pemanfaatan',
                'nama_jabatan' => 'Bidang Pemanfaatan Data dan Inovasi Pelayanan',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'bidang',
                'eselon' => 'III.c',
                'urutan' => 20
            ],
            [
                'kode_posisi' => 'koord_pemanfaatan_1',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 21
            ],
            [
                'kode_posisi' => 'koord_pemanfaatan_2',
                'nama_jabatan' => 'Koordinator / Sub Koordinator',
                'nama_pejabat' => null, // Diisi nama pejabat
                'parent_id' => null,
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => 22
            ],
            [
                'kode_posisi' => 'kf_pemanfaatan',
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => null,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => 23
            ],
        ];

        foreach ($data as $item) {
            Organisasi_Model::create($item);
        }
    }
}
