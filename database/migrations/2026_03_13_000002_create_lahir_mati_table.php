<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lahir_mati', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Foreign keys
            $table->char('layanan_id', 36);
            $table->foreign('layanan_id')
                ->references('layanan_id')
                ->on('layanan')
                ->onDelete('cascade');
            $table->char('antrian_online_id', 36)->nullable();
            
            // Form fields
            $table->string('nomor_antrian')->nullable();
            
            // Data Pemohon (Sesuai Konsep Baru)
            $table->string('nik_pemohon')->index();
            $table->string('nomor_kk_pemohon')->nullable();
            $table->string('nama_pemohon');
            $table->text('alamat_pemohon');
            $table->string('hubungan_pemohon');
            
            // Data Bayi (dari remote)
            $table->string('nama_bayi');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->datetime('tgl_lahir');
            $table->string('tempat_lahir');
            $table->string('nama_ayah');
            $table->text('nik_ayah')->nullable();
            $table->string('nama_ibu');
            $table->text('nik_ibu')->nullable();
            
            // File uploads
            $table->string('ktp_pemohon')->nullable();
            $table->string('kartu_keluarga_pemohon')->nullable();
            $table->string('ktp_saksi1')->nullable();
            $table->string('ktp_saksi2')->nullable();
            $table->string('formulir_f201')->nullable();
            $table->string('surat_keterangan_lahir_mati')->nullable();
            
            // Status dan metadata
            $table->enum('status', ['Dokumen Diterima', 'Verifikasi Data', 'Proses Cetak', 'Siap Pengambilan', 'Tolak'])
                ->default('Dokumen Diterima');
            $table->text('alasan_penolakan')->nullable();
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lahir_mati');
    }
};
