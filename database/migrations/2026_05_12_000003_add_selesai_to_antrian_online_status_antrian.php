<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE `antrian_online` MODIFY COLUMN `status_antrian` ENUM('Menunggu','Dokumen Diterima','Verifikasi Data','Proses Cetak','Siap Pengambilan','Selesai','Ditolak','Dibatalkan') NOT NULL DEFAULT 'Menunggu'");
        } catch (\Throwable $e) {
            // skip
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `antrian_online` MODIFY COLUMN `status_antrian` ENUM('Menunggu','Dokumen Diterima','Verifikasi Data','Proses Cetak','Siap Pengambilan','Ditolak','Dibatalkan') NOT NULL DEFAULT 'Menunggu'");
        } catch (\Throwable $e) {
            // skip
        }
    }
};
