<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lacak_berkas', function (Blueprint $table) {
            if (!Schema::hasColumn('lacak_berkas', 'file_berkas')) {
                $table->string('file_berkas', 500)->nullable()->after('alasan_penolakan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lacak_berkas', function (Blueprint $table) {
            if (Schema::hasColumn('lacak_berkas', 'file_berkas')) {
                $table->dropColumn('file_berkas');
            }
        });
    }
};
