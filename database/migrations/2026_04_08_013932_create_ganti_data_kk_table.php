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
        Schema::create('ganti_data_kk', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->char('layanan_id', 36);
            $table->foreign('layanan_id')
                ->references('layanan_id')
                ->on('layanan')
                ->onDelete('cascade');
            $table->string('nomor_antrian')->unique();
            $table->string('nama_pemohon');
            $table->char('nik_pemohon', 16);
            $table->string('nomor_kk_pemohon');
            $table->string('alamat_pemohon');
            $table->string('formulir_f102');
            $table->string('ktp_pemohon');
            $table->string('kk_pemohon');
            $table->string('formulir_f106');
            $table->string('surat_keterangan_perubahan');
            $table->string('pernyataan_pindah_kk')->nullable();
            $table->string('foto_wajah')->nullable();
            $table->string('alasan_penolakan')->nullable();
            $table->enum('status',['Dokumen Diterima', 'Verifikasi Data','Proses Cetak', 'Siap Pengambilan','Tolak'])->default('Dokumen Diterima');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ganti_data_kk');
    }
};
