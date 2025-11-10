<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ujian extends Model {
    use HasFactory;
    protected $fillable = ['judul', 'deskripsi', 'durasi_menit'];

    public function soals() {
        return $this->hasMany(Soal::class);
    }
}