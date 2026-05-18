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
            // Tambahkan kolom level untuk kategori jabatan (jika belum ada)
            if (!Schema::hasColumn('organisasi', 'level')) {
                $table->enum('level', ['pimpinan_utama', 'bidang', 'sub_bagian', 'koordinator', 'kelompok_fungsional'])
                      ->nullable()
                      ->after('nama_jabatan');
            }

            // Tambahkan kolom parent_id untuk relasi hierarchy (jika belum ada)
            if (!Schema::hasColumn('organisasi', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                $table->foreign('parent_id')->references('id')->on('organisasi')->onDelete('set null');
            }

            // Tambahkan kolom untuk data pejabat (jika belum ada)
            if (!Schema::hasColumn('organisasi', 'nama_pejabat')) {
                $table->string('nama_pejabat')->nullable()->after('level');
            }

            if (!Schema::hasColumn('organisasi', 'eselon')) {
                $table->string('eselon')->nullable()->after('nama_pejabat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisasi', function (Blueprint $table) {
            if (Schema::hasColumn('organisasi', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('organisasi', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('organisasi', 'nama_pejabat')) {
                $table->dropColumn('nama_pejabat');
            }
            if (Schema::hasColumn('organisasi', 'eselon')) {
                $table->dropColumn('eselon');
            }
        });
    }
};
