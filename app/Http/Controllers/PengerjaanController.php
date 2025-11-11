<?php
namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\HasilUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

class PengerjaanController extends Controller
{
    
    public function start(Ujian $ujian)
    {
        // --- Validasi Jadwal Ujian ---
        $now = now();

        if ($ujian->available_from && $now->lt($ujian->available_from)) {
            return redirect()->route('ujian.index')
                ->with('status', 'Ujian ini belum dibuka.');
        }

        if ($ujian->available_to && $now->gt($ujian->available_to)) {
            return redirect()->route('ujian.index')
                ->with('status', 'Ujian ini sudah ditutup.');
        }

        // --- Validasi apakah sudah pernah selesai ---
        $hasilSebelumnya = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNotNull('finished_at') 
                                ->first();

        if ($hasilSebelumnya) {
             return redirect()->route('pengerjaan.result', $ujian)
                 ->with('status', 'Anda sudah pernah menyelesaikan ujian ini.');
        }

        // --- Validasi Pengerjaan yang masih berlangsung ---
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
             return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)
                 ->with('status', 'Anda sudah menyelesaikan ujian ini.');
        }

        $hasilUjian->load('ujian.soals.pilihanJawabans');
        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);

        if (now()->greaterThan($endTime)) {
             return $this->forceSubmit($hasilUjian);
        }

        return view('pengerjaan.show', compact('hasilUjian', 'endTime'));
    }


    public function submit(Request $request, HasilUjian $hasilUjian)
    {
        if ($hasilUjian->user_id !== Auth::id() || $hasilUjian->finished_at) {
            abort(403, 'Akses ditolak.');
        }

        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);
        if (now()->greaterThan($endTime->addSeconds(5))) {
            return $this->forceSubmit($hasilUjian, 'Waktu Anda habis. Jawaban terakhir tidak tersimpan.');
        }

        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*' => 'nullable',
        ]);

        $ujian = $hasilUjian->ujian->load('soals.pilihanJawabans'); 
        
        $total_soal_pg = 0;
        $jawaban_benar = 0;

        foreach ($ujian->soals as $soal) {
            $jawaban_data = $request->jawaban[$soal->id] ?? null;

            if ($soal->type == 'pilihan_ganda') {
                $total_soal_pg++;
                
                $id_pilihan_user = $jawaban_data['pilihan_id'] ?? null;

                if ($id_pilihan_user) {
                    $pilihan_benar = $soal->pilihanJawabans->firstWhere('apakah_benar', true);
                    if ($pilihan_benar && $pilihan_benar->id == $id_pilihan_user) {
                        $jawaban_benar++;
                    }
                }
            }
        }

        $skor = ($total_soal_pg > 0) ? ($jawaban_benar / $total_soal_pg) * 100 : 0;
        
        $ada_esai = $ujian->soals->contains('type', 'esai');

        $hasilUjian->update([
            'skor' => $skor,
            'finished_at' => now(),
        ]);

        $pesan = 'Ujian telah selesai dikerjakan!';
        if ($ada_esai) {
            $pesan = 'Ujian selesai. Jawaban esai akan dikoreksi oleh dosen.';
        }

        return redirect()->route('pengerjaan.result', $ujian->id)->with('status', $pesan);
    }


    public function result(Ujian $ujian)
    {
        $hasil = HasilUjian::where('user_id', Auth::id())
                        ->where('ujian_id', $ujian->id)
                        ->whereNotNull('finished_at') 
                        ->latest('finished_at')
                        ->firstOrFail();

        $skor = $hasil->skor;
        return view('pengerjaan.result', compact('ujian', 'skor'));
    }


    private function forceSubmit(HasilUjian $hasilUjian, $message = 'Waktu habis! Ujian diselesaikan secara otomatis.')
    {
         if (!$hasilUjian->finished_at) {
            $hasilUjian->update([
                'skor' => $hasilUjian->skor ?? 0, 
                'finished_at' => now(),
            ]);
         }
        return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)->with('status', $message);
    }
}
