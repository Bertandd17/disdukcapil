<?php

namespace Database\Seeders;

use App\Models\Organisasi_Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganisasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data existing (urutan: hapus child dulu, baru parent)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Organisasi_Model::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 2 tahap: insert parent dulu agar parent_id bisa di-reference
        // Tahap 1: Pimpinan utama (top-level, parent_id = NULL)
        $kadis = Organisasi_Model::create([
            'kode_posisi' => 'kadis',
            'nama_jabatan' => 'Kepala Dinas',
            'nama_pejabat' => null,
            'parent_id' => null,
            'level' => 'pimpinan_utama',
            'eselon' => 'II.b',
            'urutan' => 1,
        ]);

        $sekdin = Organisasi_Model::create([
            'kode_posisi' => 'sekdin',
            'nama_jabatan' => 'Sekretaris',
            'nama_pejabat' => null,
            'parent_id' => $kadis->id,
            'level' => 'pimpinan_utama',
            'eselon' => 'III.d',
            'urutan' => 2,
        ]);

        // Tahap 2: Sub-bagian & kelompok fungsional di bawah Sekretaris
        $umum = Organisasi_Model::create([
            'kode_posisi' => 'umum_kepegawaian',
            'nama_jabatan' => 'Sub Bagian Umum dan Kepegawaian',
            'nama_pejabat' => null,
            'parent_id' => $sekdin->id,
            'level' => 'sub_bagian',
            'eselon' => 'IV.a',
            'urutan' => 3,
        ]);

        $perencanaan = Organisasi_Model::create([
            'kode_posisi' => 'perencanaan',
            'nama_jabatan' => 'Sub Bagian Perencanaan',
            'nama_pejabat' => null,
            'parent_id' => $sekdin->id,
            'level' => 'sub_bagian',
            'eselon' => 'IV.a',
            'urutan' => 4,
        ]);

        Organisasi_Model::create([
            'kode_posisi' => 'koord_sekretariat',
            'nama_jabatan' => 'Koordinator / Sub Koordinator Sekretariat',
            'nama_pejabat' => null,
            'parent_id' => $sekdin->id,
            'level' => 'koordinator',
            'eselon' => null,
            'urutan' => 5,
        ]);

        // Tahap 3: 4 Bidang (parent: Kadis langsung, sesuai struktur Disdukcapil)
        $bidafduk = Organisasi_Model::create([
            'kode_posisi' => 'bidafduk',
            'nama_jabatan' => 'Bidang Pelayanan Pendaftaran Penduduk',
            'nama_pejabat' => null,
            'parent_id' => $kadis->id,
            'level' => 'bidang',
            'eselon' => 'III.c',
            'urutan' => 6,
        ]);

        $bidcapil = Organisasi_Model::create([
            'kode_posisi' => 'bidcapil',
            'nama_jabatan' => 'Bidang Pelayanan Pencatatan Sipil',
            'nama_pejabat' => null,
            'parent_id' => $kadis->id,
            'level' => 'bidang',
            'eselon' => 'III.c',
            'urutan' => 7,
        ]);

        $bidInformasi = Organisasi_Model::create([
            'kode_posisi' => 'bid_informasi',
            'nama_jabatan' => 'Bidang Pengelolaan Informasi Adm. Kependudukan',
            'nama_pejabat' => null,
            'parent_id' => $kadis->id,
            'level' => 'bidang',
            'eselon' => 'III.c',
            'urutan' => 8,
        ]);

        $bidPemanfaatan = Organisasi_Model::create([
            'kode_posisi' => 'bid_pemanfaatan',
            'nama_jabatan' => 'Bidang Pemanfaatan Data dan Inovasi Pelayanan',
            'nama_pejabat' => null,
            'parent_id' => $kadis->id,
            'level' => 'bidang',
            'eselon' => 'III.c',
            'urutan' => 9,
        ]);

        // Tahap 4: Koordinator di bawah masing-masing Bidang
        $koordItems = [
            ['bidafduk',     'koord_dafduk_1',     'Koordinator Pendaftaran Penduduk 1',  10],
            ['bidafduk',     'koord_dafduk_2',     'Koordinator Pendaftaran Penduduk 2',  11],
            ['bidcapil',     'koord_pencatatan_1', 'Koordinator Pencatatan Sipil 1',     12],
            ['bidcapil',     'koord_pencatatan_2', 'Koordinator Pencatatan Sipil 2',     13],
            ['bid_informasi','koord_informasi_1',  'Koordinator Pengelolaan Informasi 1',14],
            ['bid_informasi','koord_informasi_2',  'Koordinator Pengelolaan Informasi 2',15],
            ['bid_pemanfaatan','koord_pemanfaatan_1','Koordinator Pemanfaatan Data 1',   16],
            ['bid_pemanfaatan','koord_pemanfaatan_2','Koordinator Pemanfaatan Data 2',   17],
        ];

        $bidangMap = [
            'bidafduk' => $bidafduk->id,
            'bidcapil' => $bidcapil->id,
            'bid_informasi' => $bidInformasi->id,
            'bid_pemanfaatan' => $bidPemanfaatan->id,
        ];

        foreach ($koordItems as [$bidangKey, $kode, $nama, $urutan]) {
            Organisasi_Model::create([
                'kode_posisi' => $kode,
                'nama_jabatan' => $nama,
                'nama_pejabat' => null,
                'parent_id' => $bidangMap[$bidangKey],
                'level' => 'koordinator',
                'eselon' => null,
                'urutan' => $urutan,
            ]);
        }

        // Tahap 5: Kelompok Jabatan Fungsional di bawah masing-masing Koordinator
        $kfItems = [
            'koord_dafduk_1'      => 'kf_dafduk',
            'koord_pencatatan_1'  => 'kf_pencatatan',
            'koord_informasi_1'   => 'kf_informasi',
            'koord_pemanfaatan_1' => 'kf_pemanfaatan',
        ];

        $koordMap = Organisasi_Model::whereIn('kode_posisi', array_keys($kfItems))
            ->pluck('id', 'kode_posisi');

        $urutanStart = 18;
        foreach ($kfItems as $parentKode => $kode) {
            $parentId = $koordMap[$parentKode] ?? null;
            if (! $parentId) {
                continue;
            }
            Organisasi_Model::create([
                'kode_posisi' => $kode,
                'nama_jabatan' => 'Kelompok Jabatan Fungsional',
                'nama_pejabat' => null,
                'parent_id' => $parentId,
                'level' => 'kelompok_fungsional',
                'eselon' => null,
                'urutan' => $urutanStart++,
            ]);
        }
    }
}
