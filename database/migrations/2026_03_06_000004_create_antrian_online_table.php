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
        Schema::create('antrian_online', function (Blueprint $table) {
            // Gunakan CHAR(36) secara eksplisit untuk UUID
            $table->char('antrian_online_id', 36)->primary();
            $table->string('nomor_antrian', 20)->unique();

            // NIK
            $table->string('nik', 16)->nullable();

            // Data personal
            $table->string('nama_lengkap', 100);
            $table->text('alamat')->nullable();

            // Foreign key ke layanan
            $table->char('layanan_id', 36);
            $table->foreign('layanan_id')
                ->references('layanan_id')
                ->on('layanan')
                ->onDelete('cascade');

            // Status antrian
            $table->enum('status_antrian', ['Menunggu', 'Dokumen Diterima', 'Verifikasi Data', 'Proses Cetak', 'Siap Pengambilan', 'Ditolak', 'Dibatalkan'])->default('Menunggu');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('nomor_antrian');
            $table->index('status_antrian');
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian_online');
    }
};
