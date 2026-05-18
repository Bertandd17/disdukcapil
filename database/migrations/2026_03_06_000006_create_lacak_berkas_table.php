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
        Schema::create('lacak_berkas', function (Blueprint $table) {
            $table->id('lacak_berkas_id');
            // Gunakan char(36) untuk UUID foreign key agar cocok dengan antrian_online
            $table->char('antrian_online_id', 36);
            $table->string('status', 100);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('antrian_online_id')
                    ->references('antrian_online_id')
                    ->on('antrian_online')
                    ->onDelete('cascade');

            $table->index('antrian_online_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lacak_berkas');
    }
};
