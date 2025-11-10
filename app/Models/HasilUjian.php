<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilUjian extends Model
{
    use HasFactory;

    /**
     * PERUBAHAN 1: Tambahkan casts untuk kolom timestamp.
     * Ini akan mengubah string dari DB menjadi objek Carbon (PHP).
     *
     * @var array
     */
    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    /**
     * PERUBAHAN 2: Tambahkan 'started_at' dan 'finished_at' ke $fillable.
     * Ini adalah perbaikan utama untuk error Anda.
     */
    protected $fillable = [
        'user_id',
        'ujian_id',
        'skor',
        'started_at',   // <-- TAMBAHKAN INI
        'finished_at',  // <-- TAMBAHKAN INI
    ];

    /**
     * Mendapatkan data user (mahasiswa) yang mengerjakan ujian.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan data ujian yang dikerjakan.
     */
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }
}