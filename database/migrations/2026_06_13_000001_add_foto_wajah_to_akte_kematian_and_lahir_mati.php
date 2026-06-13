<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akte_kematian', function (Blueprint $table) {
            if (!Schema::hasColumn('akte_kematian', 'foto_wajah')) {
                $table->string('foto_wajah')->nullable()->after('ktp_saksi2');
            }
        });

        Schema::table('lahir_mati', function (Blueprint $table) {
            if (!Schema::hasColumn('lahir_mati', 'foto_wajah')) {
                $table->string('foto_wajah')->nullable()->after('surat_keterangan_lahir_mati');
            }
        });
    }

    public function down(): void
    {
        Schema::table('akte_kematian', function (Blueprint $table) {
            if (Schema::hasColumn('akte_kematian', 'foto_wajah')) {
                $table->dropColumn('foto_wajah');
            }
        });

        Schema::table('lahir_mati', function (Blueprint $table) {
            if (Schema::hasColumn('lahir_mati', 'foto_wajah')) {
                $table->dropColumn('foto_wajah');
            }
        });
    }
};
