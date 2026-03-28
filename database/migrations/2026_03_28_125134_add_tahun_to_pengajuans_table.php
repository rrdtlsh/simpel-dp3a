<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            // Menambahkan kolom tahun setelah bidang_id
            $table->string('tahun', 4)->nullable()->after('bidang_id');
        });
    }

    public function down()
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
