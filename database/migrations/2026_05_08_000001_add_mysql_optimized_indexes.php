<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration untuk menambahkan index yang dioptimasi untuk MySQL
 * Ini akan meningkatkan performa query case-insensitive
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('antrian_online', function (Blueprint $table) {
            // Index untuk nomor_antrian case-insensitive search
            if (!$this->indexExists('antrian_online', 'idx_nomor_antrian_ci')) {
                $table->index('nomor_antrian', 'idx_nomor_antrian_ci');
            }

            // Index individual untuk NIK (untuk daily limit check)
            if (!$this->indexExists('antrian_online', 'idx_nik')) {
                $table->index('nik', 'idx_nik');
            }

            // Index untuk layanan_id
            if (!$this->indexExists('antrian_online', 'idx_layanan_id')) {
                $table->index('layanan_id', 'idx_layanan_id');
            }

            // Index untuk created_at (untuk filter tanggal)
            if (!$this->indexExists('antrian_online', 'idx_created_at')) {
                $table->index('created_at', 'idx_created_at');
            }
        });

        Schema::table('lacak_berkas', function (Blueprint $table) {
            // Index untuk status check
            if (!$this->indexExists('lacak_berkas', 'idx_status')) {
                $table->index('status', 'idx_status');
            }

            // Index untuk tanggal (untuk daily limit check)
            if (!$this->indexExists('lacak_berkas', 'idx_tanggal')) {
                $table->index('tanggal', 'idx_tanggal');
            }

            // Index untuk antrian_online_id
            if (!$this->indexExists('lacak_berkas', 'idx_lacak_antrian_id')) {
                $table->index('antrian_online_id', 'idx_lacak_antrian_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrian_online', function (Blueprint $table) {
            if ($this->indexExists('antrian_online', 'idx_nomor_antrian_ci')) {
                $table->dropIndex('idx_nomor_antrian_ci');
            }
            if ($this->indexExists('antrian_online', 'idx_nik')) {
                $table->dropIndex('idx_nik');
            }
            if ($this->indexExists('antrian_online', 'idx_layanan_id')) {
                $table->dropIndex('idx_layanan_id');
            }
            if ($this->indexExists('antrian_online', 'idx_created_at')) {
                $table->dropIndex('idx_created_at');
            }
        });

        Schema::table('lacak_berkas', function (Blueprint $table) {
            if ($this->indexExists('lacak_berkas', 'idx_status')) {
                $table->dropIndex('idx_status');
            }
            if ($this->indexExists('lacak_berkas', 'idx_tanggal')) {
                $table->dropIndex('idx_tanggal');
            }
            if ($this->indexExists('lacak_berkas', 'idx_lacak_antrian_id')) {
                $table->dropIndex('idx_lacak_antrian_id');
            }
        });
    }

    /**
     * Helper method untuk mengecek apakah index sudah ada di MySQL
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
};
