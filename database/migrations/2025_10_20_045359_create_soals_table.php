<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('soals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ujian_id')->constrained()->cascadeOnDelete();
            $t->text('pertanyaan');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('soals');
    }
};