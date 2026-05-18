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
        Schema::create('penghargaan', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('file');
            $table->string('nama');
            $table->string('deskripsi_singkat');
            $table->string('instansi')->nullable();
            $table->enum('tingkat', ['Nasional', 'Provinsi', 'Kabupaten'])->nullable();
            $table->year('tahun')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->dropColumn(['instansi', 'tingkat', 'tahun', 'lokasi']);
    }
};
