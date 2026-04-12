<?php
// FILE: database/migrations/2024_01_01_000001_create_pengumuman_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->text('konten');
            $table->string('gambar')->nullable();          // path relatif dari storage/app/public
            $table->string('badge_label', 50)->default('Info');  // misal: Penting, Sistem, Kegiatan
            $table->string('badge_color', 20)->default('blue'); // red | blue | green
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
