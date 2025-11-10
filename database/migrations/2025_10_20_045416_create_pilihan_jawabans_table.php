<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pilihan_jawabans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('soal_id')->constrained()->cascadeOnDelete();
            $t->text('teks_pilihan');
            $t->boolean('apakah_benar')->default(false);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pilihan_jawabans');
    }
};