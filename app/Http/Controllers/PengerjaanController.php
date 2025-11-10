<?php
namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\HasilUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

class PengerjaanController extends Controller
{
    // ... (fungsi start, begin, show tidak berubah) ...
    public function start(Ujian $ujian)
    {
        $hasilSebelumnya = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNotNull('finished_at') 
                                ->first();

        if ($hasilSebelumnya) {
             return redirect()->route('pengerjaan.result', $ujian)->with('status', 'Anda sudah pernah menyelesaikan ujian ini.');
        }

        $pengerjaanAktif = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNull('finished_at') 
                                ->first();
        
        if ($pengerjaanAktif) {
            return redirect()->route('pengerjaan.show', $pengerjaanAktif);
        }

        $ujian->loadCount('soals');
        return view('pengerjaan.start', compact('ujian'));
    }

    public function begin(Ujian $ujian)
    {
        $pengerjaanAktif = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNull('finished_at')
                                ->first();
        
        if ($pengerjaanAktif) {
            return redirect()->route('pengerjaan.show', $pengerjaanAktif);
        }

        $hasilUjian = HasilUjian::create([
            'user_id' => Auth::id(),
            'ujian_id' => $ujian->id,
            'started_at' => now(),
            'skor' => null, 
        ]);

        return redirect()->route('pengerjaan.show', $hasilUjian);
    }

    public function show(HasilUjian $hasilUjian)
    {
        if ($hasilUjian->user_id !== Auth::id()) {
            abort(403);
        }

        if ($hasilUjian->finished_at) {
             return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)->with('status', 'Anda sudah menyelesaikan ujian ini.');
        }

        $hasilUjian->load('ujian.soals.pilihanJawabans');
        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);

        if (now()->greaterThan($endTime)) {
             return $this->forceSubmit($hasilUjian);
        }

        return view('pengerjaan.show', compact('hasilUjian', 'endTime'));
    }

    // --- MULAI PERUBAHAN DI SINI ---
    public function submit(Request $request, HasilUjian $hasilUjian)
    {
        // 1. Validasi Keamanan
        if ($hasilUjian->user_id !== Auth::id() || $hasilUjian->finished_at) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Waktu (SERVER-SIDE)
        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);
        if (now()->greaterThan($endTime->addSeconds(5))) {
            return $this->forceSubmit($hasilUjian, 'Waktu Anda habis. Jawaban terakhir tidak tersimpan.');
        }
        
        // 3. Validasi Input (Dibuat lebih longgar untuk menerima esai)
        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable', // Izinkan null jika tidak dijawab
        ]);

        // 4. Hitung Skor (HANYA UNTUK PILIHAN GANDA)
        $ujian = $hasilUjian->ujian->load('soals.pilihanJawabans'); 
        
        $total_soal_pg = 0; // Total soal Pilihan Ganda
        $jawaban_benar = 0;

        foreach ($ujian->soals as $soal) {
            
            // Ambil data jawaban untuk soal ini
            $jawaban_data = $request->jawaban[$soal->id] ?? null;

            if ($soal->type == 'pilihan_ganda') {
                // Ini adalah soal PG
                $total_soal_pg++;
                
                $id_pilihan_user = $jawaban_data['pilihan_id'] ?? null;

                if ($id_pilihan_user) {
                    $pilihan_benar = $soal->pilihanJawabans->firstWhere('apakah_benar', true);
                    if ($pilihan_benar && $pilihan_benar->id == $id_pilihan_user) {
                        $jawaban_benar++;
                    }
                }
            } 
            
            // Nanti di sini kita akan tambahkan logika untuk menyimpan jawaban esai
            // if ($soal->type == 'esai') {
            //     $teks_jawaban = $jawaban_data['teks_jawaban'] ?? null;
            //     // ... (simpan $teks_jawaban ke tabel baru)
            // }
        }

        // Skor dihitung HANYA berdasarkan soal PG
        $skor = ($total_soal_pg > 0) ? ($jawaban_benar / $total_soal_pg) * 100 : 0;
        
        // Cek apakah ada soal esai, jika ada, skor akhir perlu ditinjau
        $ada_esai = $ujian->soals->contains('type', 'esai');

        // 5. Simpan Hasil
        $hasilUjian->update([
            'skor' => $skor, // Simpan skor PG
            'finished_at' => now(),
            // Nanti di sini kita update 'status' -> 'Menunggu Koreksi' jika $ada_esai
        ]);

        $pesan = 'Ujian telah selesai dikerjakan!';
        if ($ada_esai) {
            $pesan = 'Ujian telah selesai. Skor pilihan ganda Anda telah disimpan. Jawaban esai akan dikoreksi oleh dosen.';
        }

        return redirect()->route('pengerjaan.result', $ujian->id)->with('status', $pesan);
    }
    // --- AKHIR PERUBAHAN ---

    public function result(Ujian $ujian)
    {
        $hasil = HasilUjian::where('user_id', Auth::id())
                        ->where('ujian_id', $ujian->id)
                        ->whereNotNull('finished_at') 
                        ->latest('finished_at')
                        ->firstOrFail();

        $skor = $hasil->skor;
        // Kita bisa tambahkan logika di sini untuk menampilkan pesan 'Menunggu Koreksi'
        return view('pengerjaan.result', compact('ujian', 'skor'));
    }

    private function forceSubmit(HasilUjian $hasilUjian, $message = 'Waktu habis! Ujian diselesaikan secara otomatis.')
    {
         if (!$hasilUjian->finished_at) {
            // TODO: Seharusnya kita juga menghitung skor PG di sini jika ada jawaban parsial,
            // tapi untuk sekarang kita set 0 agar selesai.
            $hasilUjian->update([
                'skor' => $hasilUjian->skor ?? 0, 
                'finished_at' => now(),
            ]);
         }
        return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)->with('status', $message);
    }
}