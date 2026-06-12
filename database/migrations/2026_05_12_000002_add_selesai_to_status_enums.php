<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $tables = [
        'aktelahirs',
        'akte_kematian',
        'lahir_mati',
        'ganti_data_kk',
        'ganti_kepala_kk',
        'kk_hilang_rusak',
        'pisah_kk',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('Dokumen Diterima','Verifikasi Data','Proses Cetak','Siap Pengambilan','Selesai','Tolak') NOT NULL DEFAULT 'Dokumen Diterima'");
            } catch (\Throwable $e) {
                // skip if table doesn't exist
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            try {
                DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('Dokumen Diterima','Verifikasi Data','Proses Cetak','Siap Pengambilan','Tolak') NOT NULL DEFAULT 'Dokumen Diterima'");
            } catch (\Throwable $e) {
                // skip
            }
        }
    }
};
