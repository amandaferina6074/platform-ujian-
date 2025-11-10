<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // PERUBAHAN: Import Storage facade

class UjianController extends Controller
{
    public function index() {
        $ujians = Ujian::withCount('soals')->latest()->paginate(5);
        return view('ujian.index', compact('ujians'));
    }

    public function create() {
        return view('ujian.create');
    }

    public function store(Request $request) {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
        ]);
        $ujian = Ujian::create($request->all());
        return redirect()->route('ujian.show', $ujian)->with('status', 'Ujian berhasil dibuat! Silakan tambahkan soal.');
    }

    public function show(Ujian $ujian) {
        $ujian->load('soals.pilihanJawabans');
        return view('ujian.show', compact('ujian'));
    }

    public function createSoal(Ujian $ujian) {
        return view('soal.create', compact('ujian'));
    }

    public function storeSoal(Request $request, Ujian $ujian) {
        // PERUBAHAN: Tambahkan validasi untuk gambar
        $request->validate([
            'pertanyaan' => 'required|string',
            'gambar_soal' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // maks 2MB
            'pilihan' => 'required|array|min:4', // Pastikan 'pilihan' adalah array
            'pilihan.*' => 'required|string',
            'jawaban_benar' => 'required|integer|min:0|max:3', // Asumsi value 0-3
        ]);

        $path = null;
        // PERUBAHAN: Logika untuk menyimpan file
        if ($request->hasFile('gambar_soal')) {
            // 'store' akan membuat nama file unik dan menyimpannya
            // 'public/soal_images' akan disimpan di 'storage/app/public/soal_images'
            $path = $request->file('gambar_soal')->store('public/soal_images');
        }

        DB::transaction(function () use ($request, $ujian, $path) { // PERUBAHAN: 'use ($path)'
            
            // PERUBAHAN: Tambahkan 'image_path' saat membuat soal
            $soal = $ujian->soals()->create([
                'pertanyaan' => $request->pertanyaan,
                'image_path' => $path 
            ]);

            // Controller Anda menggunakan 'pilihan' sebagai array
            // dan 'jawaban_benar' sebagai index (0-3). Ini sudah benar.
            foreach ($request->pilihan as $key => $teksPilihan) {
                $soal->pilihanJawabans()->create([
                    'teks_pilihan' => $teksPilihan,
                    // Mencocokkan $key (0, 1, 2, 3) dengan $request->jawaban_benar
                    'apakah_benar' => ($key == $request->jawaban_benar),
                ]);
            }
        });
        return redirect()->route('ujian.show', $ujian)->with('status', 'Soal berhasil ditambahkan.');
    }
    
    public function destroy(Ujian $ujian) {
        
        // PERUBAHAN: Hapus juga file-file gambar terkait
        foreach ($ujian->soals as $soal) {
            if ($soal->image_path) {
                Storage::delete($soal->image_path);
            }
        }
        
        $ujian->delete();
        return redirect()->route('ujian.index')->with('status', 'Ujian berhasil dihapus.');
    }
    
    public function edit(Ujian $ujian) {}
    public function update(Request $request, Ujian $ujian) {}
}