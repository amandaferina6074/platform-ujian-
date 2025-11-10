<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            // Tambahkan kolom 'type' setelah 'image_path'
            // Kita set default-nya 'pilihan_ganda' agar soal lama Anda tetap berfungsi
            $table->string('type')->default('pilihan_ganda')->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};