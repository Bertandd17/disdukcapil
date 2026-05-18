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
        Schema::create('akte_kematian', function (Blueprint $table) {
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
            
            // Data Pemohon (Sesuai dengan controller baru)
            $table->string('nik_pemohon')->index();
            $table->string('nomor_kk_pemohon')->nullable();
            $table->string('nama_pemohon');
            $table->text('alamat_pemohon'); // Menggunakan text karena alamat bisa panjang
            $table->string('hubungan_pemohon');
            
            // Data Jenazah (dari remote)
            $table->string('nama_almarhum');
            $table->text('nik_almarhum')->nullable();
            $table->text('nik_pelapor')->nullable();
            $table->date('tgl_meninggal');
            $table->string('tempat_meninggal');
            $table->text('sebab_meninggal')->nullable();
            $table->string('nama_pelapor');
            
            // File uploads (Semua data jenazah & saksi dialihkan ke unggahan dokumen F-2.01)
            $table->string('ktp_pemohon')->nullable();
            $table->string('kartu_keluarga_pemohon')->nullable();
            $table->string('formulir_f201')->nullable();
            $table->string('surat_keterangan_kematian')->nullable();
            $table->string('ktp_almarhum')->nullable();
            $table->string('ktp_saksi1')->nullable();
            $table->string('ktp_saksi2')->nullable();
            
            // Status dan metadata
            $table->enum('status', ['Dokumen Diterima', 'Verifikasi Data', 'Proses Cetak', 'Siap Pengambilan', 'Tolak'])
                ->default('Dokumen Diterima');
            $table->text('alasan_penolakan')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akte_kematian');
    }
};
