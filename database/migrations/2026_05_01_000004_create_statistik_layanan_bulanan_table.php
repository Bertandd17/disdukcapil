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
        Schema::create('statistik_layanan_bulanan', function (Blueprint $table) {
            $table->uuid('statistik_layanan_bulanan_id')->primary();
            $table->year('tahun');
            $table->tinyInteger('bulan'); // 1-12
            $table->unsignedInteger('total_antrian')->default(0);
            $table->unsignedInteger('antrian_menunggu')->default(0);
            $table->unsignedInteger('antrian_diproses')->default(0);
            $table->unsignedInteger('antrian_selesai')->default(0);
            $table->unsignedInteger('antrian_ditolak')->default(0);
            $table->unsignedInteger('waktu_avg_penanganan_menit')->default(0);
            $table->decimal('persentase_kepuasan', 5, 2)->nullable();
            $table->boolean('is_auto_generated')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint: satu periode
            $table->unique(['tahun', 'bulan'], 'statistik_layanan_periode_unique');
            
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
        Schema::dropIfExists('statistik_layanan_bulanan');
    }
};
