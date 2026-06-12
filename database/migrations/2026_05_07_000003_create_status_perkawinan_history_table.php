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
        Schema::create('status_perkawinan_history', function (Blueprint $table) {
            $table->id()->primary();
            $table->char('pernikahan_id', 36);
            $table->string('status_sebelum', 50);
            $table->string('status_setelah', 50);
            $table->text('catatan')->nullable();
            $table->char('changed_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('pernikahan_id')
                ->references('pernikahan_id')
                ->on('layanan_pernikahan')
                ->onDelete('cascade');

            $table->index(['pernikahan_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_perkawinan_history');
    }
};
