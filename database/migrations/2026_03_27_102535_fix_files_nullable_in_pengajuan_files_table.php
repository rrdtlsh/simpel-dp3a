<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_files', function (Blueprint $table) {
            // Ubah kolom files menjadi nullable
            // Kolom akan terisi saat user upload, kosong saat admin baru buat pengajuan
            $table->json('files')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_files', function (Blueprint $table) {
            $table->json('files')->nullable(false)->change();
        });
    }
};
