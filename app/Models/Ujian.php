<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model {
    use HasFactory;

    protected $fillable = [
        'judul', 
        'deskripsi', 
        'durasi_menit',
        'available_from',
        'available_to'
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    public function soals() {
        return $this->hasMany(Soal::class);
    }
}