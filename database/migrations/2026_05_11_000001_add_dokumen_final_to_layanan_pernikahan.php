<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom dokumen final hasil penerbitan pernikahan oleh Disdukcapil:
     * - file_akta_pernikahan       : Akta Pernikahan
     * - file_kk_pasangan           : Kartu Keluarga baru pasangan suami-istri
     * - file_kk_ortu_pria          : Kartu Keluarga baru orang tua mempelai pria
     * - file_kk_ortu_wanita        : Kartu Keluarga baru orang tua mempelai wanita
     */
    public function up(): void
    {
        Schema::table('layanan_pernikahan', function (Blueprint $table) {
            $table->string('file_akta_pernikahan', 500)->nullable()->after('file_surat_keterangan');
            $table->string('file_kk_pasangan', 500)->nullable()->after('file_akta_pernikahan');
            $table->string('file_kk_ortu_pria', 500)->nullable()->after('file_kk_pasangan');
            $table->string('file_kk_ortu_wanita', 500)->nullable()->after('file_kk_ortu_pria');
            $table->timestamp('dokumen_final_uploaded_at')->nullable()->after('file_kk_ortu_wanita');
        });
    }

    public function down(): void
    {
        Schema::table('layanan_pernikahan', function (Blueprint $table) {
            $table->dropColumn([
                'file_akta_pernikahan',
                'file_kk_pasangan',
                'file_kk_ortu_pria',
                'file_kk_ortu_wanita',
                'dokumen_final_uploaded_at',
            ]);
        });
    }
};
