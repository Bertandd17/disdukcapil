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
        Schema::create('aktelahirs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->char('layanan_id', 36);
            $table->foreign('layanan_id')
                ->references('layanan_id')
                ->on('layanan')
                ->onDelete('cascade');
            $table->string('nomor_antrian');
            $table->string('nama_pemohon');
            $table->char('nik_pemohon', 16);
            $table->string('nomor_kk_pemohon');
            $table->string('alamat');
            $table->string('formulir_f201');
            $table->string('ktp_pemohon');
            $table->string('ktp_saksi1');
            $table->string('ktp_saksi2');
            $table->string('kk_pemohon');
            $table->string('file_surat_lahir');
            $table->string('file_buku_nikah');
            $table->string('file_sptjm_kelahiran')->nullable();
            $table->string('file_sptjm_pasutri')->nullable();
            $table->string('file_berita_acara_polisi')->nullable();
            $table->string('foto_wajah')->nullable();
            $table->string('alasan_penolakan')->nullable();
            $table->enum('status',['Dokumen Diterima', 'Verifikasi Data','Proses Cetak', 'Siap Pengambilan','Tolak'])->default('Dokumen Diterima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktelahirs');
    }
};
