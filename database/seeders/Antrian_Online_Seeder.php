<?php

namespace Database\Seeders;

use App\Models\Antrian_Online_Model;
use App\Models\Lacak_Berkas_Model;
use App\Models\Layanan_Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Antrian_Online_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $layanan = Layanan_Model::first();

        if (!$layanan) {
            $this->command->warn('Tidak ada layanan ditemukan. Jalankan Layanan_Seeder terlebih dahulu.');
            return;
        }

        $antrian_data = [
            [
                'nomor_antrian' => 'ABC-001-001',
                'nama_lengkap' => 'Budi Santoso',
                'status_antrian' => 'Menunggu',
            ],
            [
                'nomor_antrian' => 'ABC-002-002',
                'nama_lengkap' => 'Siti Aminah',
                'status_antrian' => 'Verifikasi Data',
            ],
            [
                'nomor_antrian' => 'ABC-003-003',
                'nama_lengkap' => 'Ahmad Dahlan',
                'status_antrian' => 'Proses Cetak',
            ],
            [
                'nomor_antrian' => 'ABC-004-004',
                'nama_lengkap' => 'Dewi Sartika',
                'status_antrian' => 'Siap Pengambilan',
            ],
            [
                'nomor_antrian' => 'ABC-005-005',
                'nama_lengkap' => 'Rudi Hartono',
                'status_antrian' => 'Menunggu',
            ],
        ];

        foreach ($antrian_data as $data) {
            // Generate UUID secara eksplisit
            $uuid = (string) Str::uuid();

            // Buat antrian dengan UUID yang sudah digenerate
            $antrian = Antrian_Online_Model::create([
                'antrian_online_id' => $uuid,
                'nomor_antrian' => $data['nomor_antrian'],
                'nama_lengkap' => $data['nama_lengkap'],
                'layanan_id' => $layanan->layanan_id,
                'status_antrian' => $data['status_antrian'],
            ]);

            // Buat riwayat lacak berkas sesuai status
            $this->createLacakBerkas($uuid, $data['status_antrian']);
        }

        $this->command->info('✓ Berhasil membuat ' . count($antrian_data) . ' data antrian untuk testing');
    }

    /**
     * Create lacak berkas berdasarkan status antrian
     *
     * @param  string  $antrianId
     * @param  string  $status
     * @return void
     */
    private function createLacakBerkas(string $antrianId, string $status): void
    {
        $lacakData = match($status) {
            'Menunggu' => [
                ['status' => 'Menunggu', 'tanggal' => now(), 'keterangan' => 'Antrian berhasil dibuat. Menunggu dokumen diterima oleh admin.'],
            ],
            'Verifikasi Data' => [
                ['status' => 'Menunggu', 'tanggal' => now()->subDays(2), 'keterangan' => 'Antrian berhasil dibuat. Menunggu dokumen diterima oleh admin.'],
                ['status' => 'Dokumen Diterima', 'tanggal' => now()->subDay(), 'keterangan' => 'Dokumen diterima, menunggu verifikasi'],
                ['status' => 'Verifikasi Data', 'tanggal' => now(), 'keterangan' => 'Data sedang diverifikasi oleh admin'],
            ],
            'Proses Cetak' => [
                ['status' => 'Menunggu', 'tanggal' => now()->subDays(4), 'keterangan' => 'Antrian berhasil dibuat. Menunggu dokumen diterima oleh admin.'],
                ['status' => 'Dokumen Diterima', 'tanggal' => now()->subDays(3), 'keterangan' => 'Dokumen diterima, menunggu verifikasi'],
                ['status' => 'Verifikasi Data', 'tanggal' => now()->subDays(2), 'keterangan' => 'Data sedang diverifikasi oleh admin'],
                ['status' => 'Proses Cetak', 'tanggal' => now()->subDay(), 'keterangan' => 'Dokumen sedang dalam proses cetak'],
            ],
            'Siap Pengambilan' => [
                ['status' => 'Menunggu', 'tanggal' => now()->subDays(5), 'keterangan' => 'Antrian berhasil dibuat. Menunggu dokumen diterima oleh admin.'],
                ['status' => 'Dokumen Diterima', 'tanggal' => now()->subDays(4), 'keterangan' => 'Dokumen diterima, menunggu verifikasi'],
                ['status' => 'Verifikasi Data', 'tanggal' => now()->subDays(3), 'keterangan' => 'Data sedang diverifikasi oleh admin'],
                ['status' => 'Proses Cetak', 'tanggal' => now()->subDays(2), 'keterangan' => 'Dokumen sedang dalam proses cetak'],
                ['status' => 'Siap Pengambilan', 'tanggal' => now()->subDay(), 'keterangan' => 'Dokumen siap diambil oleh pemohon'],
            ],
            default => [],
        };

        foreach ($lacakData as $data) {
            Lacak_Berkas_Model::create([
                'antrian_online_id' => $antrianId,
                'status' => $data['status'],
                'tanggal' => $data['tanggal']->format('Y-m-d'),
                'keterangan' => $data['keterangan'],
            ]);
        }
    }
}
