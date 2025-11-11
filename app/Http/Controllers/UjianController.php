<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\Soal; // Pastikan Soal model di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Pastikan Storage di-import

class UjianController extends Controller
{
    /**
     * FUNGSI YANG HILANG: Menampilkan daftar ujian (Dashboard).
     */
    public function index()
    {
        // Ambil semua ujian, hitung jumlah soalnya, urutkan dari terbaru
        $ujians = Ujian::withCount('soals')->latest()->paginate(5);
        return view('ujian.index', compact('ujians'));
    }

    /**
     * FUNGSI YANG HILANG: Menampilkan form buat ujian baru.
     */
    public function create()
    {
        return view('ujian.create');
    }

    /**
     * FUNGSI YANG HILANG: Menyimpan ujian baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi_menit' => 'required|integer|min:1',
            'available_from' => 'required|date',
            'available_to' => 'required|date|after:available_from',
        ]);
        
        $ujian = Ujian::create($request->all());
        return redirect()->route('ujian.show', $ujian)->with('status', 'Ujian berhasil dibuat! Silakan tambahkan soal.');
    }

    /**
     * Menampilkan detail ujian dan daftar soalnya.
     */
    public function show(Ujian $ujian)
    {
        $ujian->load('soals.pilihanJawabans');
        return view('ujian.show', compact('ujian'));
    }

    /**
     * Menampilkan form untuk menambah soal baru ke ujian.
     */
    public function createSoal(Ujian $ujian)
    {
        return view('soal.create', compact('ujian'));
    }

    /**
     * Menyimpan soal baru (PG/Esai/Gambar).
     */
    public function storeSoal(Request $request, Ujian $ujian)
    {
        $request->validate([
            'type' => 'required|in:pilihan_ganda,esai',
            'pertanyaan' => 'required|string',
            'gambar_soal' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'pilihan' => 'required_if:type,pilihan_ganda|array|min:4|nullable',
            'pilihan.*' => 'required_if:type,pilihan_ganda|string|nullable',
            'jawaban_benar' => 'required_if:type,pilihan_ganda|integer|min:0|max:3|nullable',
        ]);

        $path = null;
        if ($request->hasFile('gambar_soal')) {
            // Menggunakan disk 'public_uploads' yang kita buat
            $path = $request->file('gambar_soal')->store('soal_images', 'public_uploads');
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
    
    /**
     * Menghapus ujian beserta semua soal dan gambarnya.
     */
    public function destroy(Ujian $ujian)
    {
        foreach ($ujian->soals as $soal) {
            if ($soal->image_path) {
                Storage::disk('public_uploads')->delete($soal->image_path);
            }
        }
        $ujian->delete();
        return redirect()->route('ujian.index')->with('status', 'Ujian berhasil dihapus.');
    }

    /**
     * Menampilkan form untuk edit soal.
     */
    public function editSoal(Soal $soal)
    {
        $soal->load('pilihanJawabans');
        return view('soal.edit', compact('soal'));
    }

    /**
     * Mengupdate soal di database.
     */
    public function updateSoal(Request $request, Soal $soal)
    {
        $request->validate([
            'type' => 'required|in:pilihan_ganda,esai',
            'pertanyaan' => 'required|string',
            'gambar_soal' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'pilihan' => 'required_if:type,pilihan_ganda|array|min:4|nullable',
            'pilihan.*' => 'required_if:type,pilihan_ganda|string|nullable',
            'jawaban_benar' => 'required_if:type,pilihan_ganda|integer|min:0|max:3|nullable',
        ]);

        DB::transaction(function () use ($request, $soal) {
            
            $soal->pertanyaan = $request->pertanyaan;
            $soal->type = $request->type;

            if ($request->has('hapus_gambar') && $soal->image_path) {
                Storage::disk('public_uploads')->delete($soal->image_path);
                $soal->image_path = null;
            } 
            if ($request->hasFile('gambar_soal')) {
                if ($soal->image_path) {
                    Storage::disk('public_uploads')->delete($soal->image_path);
                }
                $soal->image_path = $request->file('gambar_soal')->store('soal_images', 'public_uploads');
            }

            $soal->save(); 

            $soal->pilihanJawabans()->delete();

            if ($request->type === 'pilihan_ganda') {
                foreach ($request->pilihan as $key => $teksPilihan) {
                    $soal->pilihanJawabans()->create([
                        'teks_pilihan' => $teksPilihan,
                        'apakah_benar' => ($key == $request->jawaban_benar),
                    ]);
                }
            }
        });

        return redirect()->route('ujian.show', $soal->ujian_id)->with('status', 'Soal berhasil diperbarui.');
    }

    /**
     * Menghapus soal dari database.
     */
    public function destroySoal(Soal $soal)
    {
        $ujian_id = $soal->ujian_id;

        if ($soal->image_path) {
            Storage::disk('public_uploads')->delete($soal->image_path);
        }

        $soal->delete();

        return redirect()->route('ujian.show', $ujian_id)->with('status', 'Soal berhasil dihapus.');
    }
    
    public function edit(Ujian $ujian) {}
    public function update(Request $request, Ujian $ujian) {}
}