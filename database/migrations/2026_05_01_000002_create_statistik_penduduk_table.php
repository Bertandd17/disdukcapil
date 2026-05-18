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
        Schema::create('statistik_penduduk', function (Blueprint $table) {
            $table->uuid('statistik_penduduk_id')->primary();
            $table->uuid('kecamatan_id');
            $table->year('tahun');
            $table->unsignedInteger('total_penduduk')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('kecamatan_id')
                ->references('kecamatan_id')
                ->on('kecamatan')
                ->onDelete('cascade');
            
            $table->unique(['kecamatan_id', 'tahun'], 'statistik_penduduk_kec_tahun_unique');
            
            // Indexes
            $table->index('tahun');
            $table->index('kecamatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistik_penduduk');
    }
};
