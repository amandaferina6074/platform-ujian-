<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilihanJawaban extends Model {
    use HasFactory;
    protected $fillable = ['soal_id', 'teks_pilihan', 'apakah_benar'];

    public function soal() {
        return $this->belongsTo(Soal::class);
    }
}