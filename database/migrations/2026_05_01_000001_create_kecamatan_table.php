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
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->uuid('kecamatan_id')->primary();
            $table->string('kode_kecamatan', 10)->unique();
            $table->string('nama_kecamatan', 100)->unique();
            $table->timestamps();
            
            // Indexes
            $table->index('kode_kecamatan');
            $table->index('nama_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
