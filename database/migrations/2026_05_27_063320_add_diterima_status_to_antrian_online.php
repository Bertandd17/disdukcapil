<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'Diterima' to existing enum without removing existing values
        // Enum currently has 9 values from 2026_05_23 migration
        Schema::table('antrian_online', function (Blueprint $table) {
            $table->enum('status_antrian', [
                'Menunggu',
                'Dokumen Diterima',
                'Verifikasi Data',
                'Proses Cetak',
                'Siap Pengambilan',
                'Selesai',
                'Digunakan',
                'Ditolak',
                'Dibatalkan',
                'Diterima',
            ])->default('Menunggu')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrian_online', function (Blueprint $table) {
            $table->enum('status_antrian', [
                'Menunggu',
                'Dokumen Diterima',
                'Verifikasi Data',
                'Proses Cetak',
                'Siap Pengambilan',
                'Selesai',
                'Digunakan',
                'Ditolak',
                'Dibatalkan',
            ])->default('Menunggu')->change();
        });
    }
};
