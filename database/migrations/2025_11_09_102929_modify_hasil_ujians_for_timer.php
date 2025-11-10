<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            // Waktu mulai ujian, boleh null dulu agar SQLite tidak error
            $table->timestamp('started_at')->nullable()->after('ujian_id');

            // Waktu selesai, boleh null karena belum tentu ujian selesai
            $table->timestamp('finished_at')->nullable()->after('skor');

            // Skor boleh null
            $table->integer('skor')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hasil_ujians', function (Blueprint $table) {
            $table->dropColumn('started_at');
            $table->dropColumn('finished_at');

            $table->integer('skor')->nullable(false)->change();
        });
    }
};
