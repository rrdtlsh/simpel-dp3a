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
        // 1. create_pug_komponen_table
        Schema::create('pug_komponen', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10);                    // e.g. "1", "2"
            $table->string('nama');                        // e.g. "Regulasi/Kebijakan tentang Penyelenggaraan PUG"
            $table->string('level')->default('Kab/Kota');   // Kab/Kota | Provinsi
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 2. create_pug_indikator_table
        Schema::create('pug_indikator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_id')->constrained('pug_komponen')->onDelete('cascade');
            $table->string('kode', 10);                    // e.g. "1.1", "1.2"
            $table->string('nama');
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 3. create_pug_pertanyaan_table
        Schema::create('pug_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('pug_indikator')->onDelete('cascade');
            $table->string('kode', 20);                    // e.g. "1.1"
            $table->text('pertanyaan');
            $table->decimal('skor_maksimal', 8, 2)->default(0);
            $table->json('pilihan_jawaban');               // [{label, skor, sub_pilihan:[{label,skor}]}]
            $table->text('petunjuk')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 4. create_pug_jawaban_table
        Schema::create('pug_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanyaan_id')->constrained('pug_pertanyaan')->onDelete('cascade');
            $table->integer('tahun');
            $table->string('jawaban_kode')->nullable();     // kode pilihan yang dipilih
            $table->text('jawaban_label')->nullable();      // label lengkap jawaban
            $table->text('catatan')->nullable();
            $table->decimal('skor', 8, 2)->default(0);
            $table->enum('status', ['belum', 'diisi', 'disetujui', 'ditolak'])->default('belum');
            $table->foreignId('diisi_oleh')->nullable()->constrained('users');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->timestamp('diverifikasi_at')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
            $table->unique(['pertanyaan_id', 'tahun']);
        });

        // 5. create_pug_jawaban_lampiran_table
        Schema::create('pug_jawaban_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_id')->constrained('pug_jawaban')->onDelete('cascade');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable(); // bytes
            $table->foreignId('diupload_oleh')->constrained('users');
            $table->timestamps();
        });

        // 6. create_pug_audit_log_table
        Schema::create('pug_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_id')->constrained('pug_jawaban')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('aksi');                        // "isi_jawaban" | "ubah_jawaban" | "setujui" | "tolak" | "upload_lampiran"
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        // 7. create_pug_jawaban_versi_table
        Schema::create('pug_jawaban_versi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jawaban_id')->constrained('pug_jawaban')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('versi');
            $table->string('jawaban_kode')->nullable();
            $table->text('jawaban_label')->nullable();
            $table->text('catatan')->nullable();
            $table->decimal('skor', 8, 2)->default(0);
            $table->enum('status', ['belum', 'diisi', 'disetujui', 'ditolak'])->default('belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // URUTAN PENGHAPUSAN HARUS TERBALIK DARI PEMBUATAN (Untuk menghindari error Foreign Key)
        Schema::dropIfExists('pug_jawaban_versi');
        Schema::dropIfExists('pug_audit_log');
        Schema::dropIfExists('pug_jawaban_lampiran');
        Schema::dropIfExists('pug_jawaban');
        Schema::dropIfExists('pug_pertanyaan');
        Schema::dropIfExists('pug_indikator');
        Schema::dropIfExists('pug_komponen');
    }
};
