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
        Schema::create('layanan_pernikahan', function (Blueprint $table) {
            // Primary Key - UUID
            $table->char('pernikahan_id', 36)->primary();

            // Nomor Antrian
            $table->string('nomor_antrian', 20)->unique();

            // User pemohon
            $table->char('user_id', 36)->nullable()->index();

            // Data Pemohon
            $table->string('nama_pemohon', 100);
            $table->string('nik_pemohon', 16)->nullable();
            $table->text('alamat_pemohon')->nullable();

            // Data Mempelai Pria
            $table->string('nama_mempelai_pria', 100);
            $table->string('nik_mempelai_pria', 16);
            $table->string('tempat_lahir_mempelai_pria', 100)->nullable();
            $table->date('tanggal_lahir_mempelai_pria')->nullable();
            $table->string('agama_mempelai_pria', 50)->nullable();
            $table->text('alamat_mempelai_pria')->nullable();
            $table->string('pekerjaan_mempelai_pria', 100)->nullable();

            // Data Mempelai Wanita
            $table->string('nama_mempelai_wanita', 100);
            $table->string('nik_mempelai_wanita', 16);
            $table->string('tempat_lahir_mempelai_wanita', 100)->nullable();
            $table->date('tanggal_lahir_mempelai_wanita')->nullable();
            $table->string('agama_mempelai_wanita', 50)->nullable();
            $table->text('alamat_mempelai_wanita')->nullable();
            $table->string('pekerjaan_mempelai_wanita', 100)->nullable();

            // Data Orang Tua Pria
            $table->string('nama_ayah_pria', 100)->nullable();
            $table->string('nik_ayah_pria', 16)->nullable();
            $table->string('tempat_lahir_ayah_pria', 100)->nullable();
            $table->date('tanggal_lahir_ayah_pria')->nullable();
            $table->text('alamat_ayah_pria')->nullable();

            // Data Orang Tua Wanita
            $table->string('nama_ibu_pria', 100)->nullable();
            $table->string('nik_ibu_pria', 16)->nullable();
            $table->string('tempat_lahir_ibu_pria', 100)->nullable();
            $table->date('tanggal_lahir_ibu_pria')->nullable();
            $table->text('alamat_ibu_pria')->nullable();

            // Saksi 1
            $table->string('nama_saksi_1', 100);
            $table->string('nik_saksi_1', 16);
            $table->string('tempat_lahir_saksi_1', 100)->nullable();
            $table->date('tanggal_lahir_saksi_1')->nullable();
            $table->text('alamat_saksi_1')->nullable();

            // Saksi 2
            $table->string('nama_saksi_2', 100);
            $table->string('nik_saksi_2', 16);
            $table->string('tempat_lahir_saksi_2', 100)->nullable();
            $table->date('tanggal_lahir_saksi_2')->nullable();
            $table->text('alamat_saksi_2')->nullable();

            // Gereja/Lembaga Keagamaan
            $table->char('keagamaan_id', 36)->nullable()->index();
            $table->string('nama_gereja', 100);

            // Tanggal & Lokasi
            $table->date('tanggal_perkawinan');

            // Status
            $table->enum('status', [
                'MENUNGGU_KONFIRMASI_KEAGAMAAN',
                'DITOLAK_KEAGAMAAN',
                'MENUNGGU_APPROVE_TANGGAL',
                'TANGGAL_DITOLAK',
                'TANGGAL_DISETUJUI',
                'DOKUMEN_DIUPLOAD_MENUNGGU_VERIFIKASI',
                'DOKUMEN_PERLU_PERBAIKAN',
                'DOKUMEN_DIVERIFIKASI',
                'SELESAI'
            ])->default('MENUNGGU_KONFIRMASI_KEAGAMAAN');

            // Catatan
            $table->text('catatan_keagamaan')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->text('alasan_ditolak')->nullable();

            // Berkas
            $table->string('file_berkas_acara')->nullable();
            $table->string('file_surat_keterangan')->nullable();

            // Timestamps
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // Indexes
            $table->index('nomor_antrian');
            $table->index('status');
            $table->index('nik_pemohon');
            $table->index('nik_mempelai_pria');
            $table->index('nik_mempelai_wanita');
            $table->index('tanggal_perkawinan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_pernikahan');
    }
};
