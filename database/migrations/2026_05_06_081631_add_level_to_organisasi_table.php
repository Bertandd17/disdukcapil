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
        Schema::table('organisasi', function (Blueprint $table) {
            if (!Schema::hasColumn('organisasi', 'level')) {
                $table->enum('level', ['pimpinan_utama', 'bidang', 'sub_bagian', 'koordinator', 'kelompok_fungsional'])
                      ->nullable()
                      ->after('nama_jabatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisasi', function (Blueprint $table) {
            if (Schema::hasColumn('organisasi', 'level')) {
                $table->dropColumn('level');
            }
        });
    }
};
