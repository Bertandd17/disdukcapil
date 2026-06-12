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
        Schema::create('dokumen_pernikahan', function (Blueprint $table) {
            $table->id()->primary();
            $table->char('pernikahan_id', 36);
            $table->string('jenis_dokumen', 50);
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 50);
            $table->unsignedBigInteger('file_size');
            $table->enum('status', ['UPLOADED', 'DIVERIFIKASI', 'DITOLAK'])->default('UPLOADED');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();

            $table->foreign('pernikahan_id')
                ->references('pernikahan_id')
                ->on('layanan_pernikahan')
                ->onDelete('cascade');

            $table->index(['pernikahan_id', 'jenis_dokumen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pernikahan');
    }
};
