<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanan_pernikahan', function (Blueprint $table) {
            // Tambahkan kolom untuk path file KTP
            $table->string('file_ktp_mempelai_pria')->nullable()->after('pekerjaan_mempelai_pria');
            $table->string('file_ktp_mempelai_wanita')->nullable()->after('pekerjaan_mempelai_wanita');
            $table->string('file_ktp_saksi_1')->nullable()->after('alamat_saksi_1');
            $table->string('file_ktp_saksi_2')->nullable()->after('alamat_saksi_2');
        });
    }

    public function down(): void
    {
        Schema::table('layanan_pernikahan', function (Blueprint $table) {
            $table->dropColumn(['file_ktp_mempelai_pria', 'file_ktp_mempelai_wanita', 'file_ktp_saksi_1', 'file_ktp_saksi_2']);
        });
    }
};
