<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('statistik_dokumen', 'jumlah_ktp')) {
            DB::statement('ALTER TABLE statistik_dokumen CHANGE jumlah_ktp jumlah_lahir_mati INT UNSIGNED NOT NULL DEFAULT 0');
        }
        if (Schema::hasColumn('statistik_dokumen', 'jumlah_kia')) {
            DB::statement('ALTER TABLE statistik_dokumen CHANGE jumlah_kia jumlah_pernikahan INT UNSIGNED NOT NULL DEFAULT 0');
        }

        Schema::table('statistik_layanan_bulanan', function (Blueprint $table) {
            if (!Schema::hasColumn('statistik_layanan_bulanan', 'jumlah_kk')) {
                $table->unsignedInteger('jumlah_kk')->default(0)->after('bulan');
            }
            if (!Schema::hasColumn('statistik_layanan_bulanan', 'jumlah_kelahiran')) {
                $table->unsignedInteger('jumlah_kelahiran')->default(0)->after('jumlah_kk');
            }
            if (!Schema::hasColumn('statistik_layanan_bulanan', 'jumlah_kematian')) {
                $table->unsignedInteger('jumlah_kematian')->default(0)->after('jumlah_kelahiran');
            }
            if (!Schema::hasColumn('statistik_layanan_bulanan', 'jumlah_lahir_mati')) {
                $table->unsignedInteger('jumlah_lahir_mati')->default(0)->after('jumlah_kematian');
            }
            if (!Schema::hasColumn('statistik_layanan_bulanan', 'jumlah_pernikahan')) {
                $table->unsignedInteger('jumlah_pernikahan')->default(0)->after('jumlah_lahir_mati');
            }
        });
    }

    public function down(): void
    {
        Schema::table('statistik_layanan_bulanan', function (Blueprint $table) {
            $columns = ['jumlah_kk', 'jumlah_kelahiran', 'jumlah_kematian', 'jumlah_lahir_mati', 'jumlah_pernikahan'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('statistik_layanan_bulanan', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('statistik_dokumen', 'jumlah_lahir_mati')) {
            DB::statement('ALTER TABLE statistik_dokumen CHANGE jumlah_lahir_mati jumlah_ktp INT UNSIGNED NOT NULL DEFAULT 0');
        }
        if (Schema::hasColumn('statistik_dokumen', 'jumlah_pernikahan')) {
            DB::statement('ALTER TABLE statistik_dokumen CHANGE jumlah_pernikahan jumlah_kia INT UNSIGNED NOT NULL DEFAULT 0');
        }
    }
};
