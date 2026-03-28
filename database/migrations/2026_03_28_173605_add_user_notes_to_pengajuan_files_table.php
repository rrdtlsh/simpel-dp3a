<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengajuan_files', function (Blueprint $table) {
            $table->text('user_notes')->nullable()->after('admin_notes');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_files', function (Blueprint $table) {
            $table->dropColumn('user_notes');
        });
    }
};
