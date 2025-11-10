<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;
    
    protected $fillable = ['ujian_id', 'pertanyaan', 'image_path', 'type'];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }

    public function pilihanJawabans()
    {
        return $this->hasMany(PilihanJawaban::class);
    }
}