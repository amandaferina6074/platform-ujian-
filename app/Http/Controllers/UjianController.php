<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UjianController extends Controller
{
    // ... (fungsi index, create, store, show, createSoal tidak berubah) ...
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
        
        $request->validate([
            'type' => 'required|in:pilihan_ganda,esai',
            'pertanyaan' => 'required|string',
            'gambar_soal' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // PERBAIKAN: Tambahkan 'nullable'
            'pilihan' => 'required_if:type,pilihan_ganda|array|min:4|nullable',
            'pilihan.*' => 'required_if:type,pilihan_ganda|string|nullable',
            'jawaban_benar' => 'required_if:type,pilihan_ganda|integer|min:0|max:3|nullable',
        ]);

        $path = null;
        if ($request->hasFile('gambar_soal')) {
            $path = $request->file('gambar_soal')->store('public/soal_images');
        }

        DB::transaction(function () use ($request, $ujian, $path) { 
            
            $soal = $ujian->soals()->create([
                'pertanyaan' => $request->pertanyaan,
                'image_path' => $path,
                'type' => $request->type,
            ]);

            if ($request->type === 'pilihan_ganda') {
                foreach ($request->pilihan as $key => $teksPilihan) {
                    $soal->pilihanJawabans()->create([
                        'teks_pilihan' => $teksPilihan,
                        'apakah_benar' => ($key == $request->jawaban_benar),
                    ]);
                }
            }
        });
        return redirect()->route('ujian.show', $ujian)->with('status', 'Soal berhasil ditambahkan.');
    }
    
    public function destroy(Ujian $ujian) {
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