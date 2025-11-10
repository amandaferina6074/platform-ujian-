<?php

use Illuminate\Support\Facades\Route;

// Import semua controller yang kita butuhkan
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UjianController;
use App\Http\Controllers\PengerjaanController;

// Import kelas middleware secara langsung
use App\Http\Middleware\CheckRole;


// Halaman utama
Route::get('/', [UjianController::class, 'index'])->middleware(['auth', 'verified'])->name('ujian.index');
Route::get('/dashboard', [UjianController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Rute Profile bawaan Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- Grup Route untuk Dosen ---
Route::middleware(['auth', CheckRole::class . ':dosen'])->group(function() {
    Route::get('/ujian/create', [UjianController::class, 'create'])->name('ujian.create');
    Route::post('/ujian', [UjianController::class, 'store'])->name('ujian.store');
    Route::get('/ujian/{ujian}', [UjianController::class, 'show'])->name('ujian.show');
    Route::delete('/ujian/{ujian}', [UjianController::class, 'destroy'])->name('ujian.destroy');
    
    Route::get('/ujian/{ujian}/soal/create', [UjianController::class, 'createSoal'])->name('soal.create');
    Route::post('/ujian/{ujian}/soal', [UjianController::class, 'storeSoal'])->name('soal.store');
});


// --- Grup Route untuk Mahasiswa (VERSI BARU DENGAN TIMER) ---
Route::middleware(['auth', CheckRole::class . ':mahasiswa'])->prefix('pengerjaan')->group(function() {
    
    // Rute ini sekarang menampilkan halaman KONFIRMASI
    Route::get('/{ujian}/start', [PengerjaanController::class, 'start'])->name('pengerjaan.start');
    
    // Rute BARU untuk MEREKAM waktu mulai dan me-redirect ke halaman soal
    Route::post('/{ujian}/begin', [PengerjaanController::class, 'begin'])->name('pengerjaan.begin');

    // Rute BARU untuk MENAMPILKAN soal (yang ada timernya)
    // URL ini yang cocok dengan /pengerjaan/1
    Route::get('/{hasilUjian}', [PengerjaanController::class, 'show'])->name('pengerjaan.show');
    
    // Rute submit sekarang menggunakan ID pengerjaan (HasilUjian)
    Route::post('/{hasilUjian}/submit', [PengerjaanController::class, 'submit'])->name('pengerjaan.submit');
    
    // Rute result tetap sama
    Route::get('/{ujian}/result', [PengerjaanController::class, 'result'])->name('pengerjaan.result');
});


// Rute Autentikasi dari Breeze
require __DIR__.'/auth.php';