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
        Schema::create('statistik_dokumen', function (Blueprint $table) {
            $table->uuid('statistik_dokumen_id')->primary();
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->unsignedInteger('jumlah_kk')->default(0);
            $table->unsignedInteger('jumlah_akte_lahir')->default(0);
            $table->unsignedInteger('jumlah_akte_kematian')->default(0);
            $table->unsignedInteger('jumlah_ktp')->default(0);
            $table->unsignedInteger('jumlah_kia')->default(0);
            $table->unsignedInteger('total_dokumen')->default(0);
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint: satu periode
            $table->unique(['tahun', 'bulan'], 'statistik_dokumen_periode_unique');
            
            // Indexes
            $table->index('tahun');
            $table->index('bulan');
            $table->index('is_auto_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistik_dokumen');
    }
};
