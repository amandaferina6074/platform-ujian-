<?php
namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\HasilUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // PERUBAHAN: Import Carbon

class PengerjaanController extends Controller
{
    /**
     * PERUBAHAN: Menampilkan halaman KONFIRMASI.
     * Dulu: menampilkan soal.
     */
    public function start(Ujian $ujian)
    {
        // Cek apakah mahasiswa sudah pernah mengerjakan dan selesai
        $hasilSebelumnya = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNotNull('finished_at') // Cek yg sudah selesai
                                ->first();

        if ($hasilSebelumnya) {
             return redirect()->route('pengerjaan.result', $ujian)->with('status', 'Anda sudah pernah menyelesaikan ujian ini.');
        }

        // Cek apakah mahasiswa sedang aktif mengerjakan (misal: refresh tab)
        $pengerjaanAktif = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNull('finished_at') // Cek yg belum selesai
                                ->first();
        
        if ($pengerjaanAktif) {
            // Jika ada, langsung arahkan ke halaman soal
            return redirect()->route('pengerjaan.show', $pengerjaanAktif);
        }

        // Jika belum, tampilkan halaman konfirmasi
        $ujian->loadCount('soals');
        return view('pengerjaan.start', compact('ujian'));
    }

    /**
     * PERUBAHAN: Method BARU untuk MEREKAM waktu mulai.
     */
    public function begin(Ujian $ujian)
    {
        // Cek lagi untuk menghindari double-click
        $pengerjaanAktif = HasilUjian::where('user_id', Auth::id())
                                ->where('ujian_id', $ujian->id)
                                ->whereNull('finished_at')
                                ->first();
        
        if ($pengerjaanAktif) {
            return redirect()->route('pengerjaan.show', $pengerjaanAktif);
        }

        // Buat record pengerjaan baru
        $hasilUjian = HasilUjian::create([
            'user_id' => Auth::id(),
            'ujian_id' => $ujian->id,
            'started_at' => now(),
            'skor' => null, // Skor masih null
        ]);

        return redirect()->route('pengerjaan.show', $hasilUjian);
    }

    /**
     * PERUBAHAN: Method BARU untuk MENAMPILKAN soal + timer.
     * Menggunakan model binding HasilUjian.
     */
    public function show(HasilUjian $hasilUjian)
    {
        // Pastikan user ini yang punya pengerjaan
        if ($hasilUjian->user_id !== Auth::id()) {
            abort(403);
        }

        // Pastikan ujian belum selesai
        if ($hasilUjian->finished_at) {
             return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)->with('status', 'Anda sudah menyelesaikan ujian ini.');
        }

        // Load relasi yg dibutuhkan
        $hasilUjian->load('ujian.soals.pilihanJawabans');

        // Hitung waktu selesai (Server-side)
        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);

        // Jika waktu sudah habis saat halaman di-load
        if (now()->greaterThan($endTime)) {
            // Paksa submit (meski belum tentu ada jawaban)
             return $this->forceSubmit($hasilUjian);
        }

        return view('pengerjaan.show', compact('hasilUjian', 'endTime'));
    }


    /**
     * PERUBAHAN: Logika submit sekarang divalidasi oleh server.
     * Menggunakan model binding HasilUjian.
     */
    public function submit(Request $request, HasilUjian $hasilUjian)
    {
        // 1. Validasi Keamanan
        if ($hasilUjian->user_id !== Auth::id() || $hasilUjian->finished_at) {
            abort(403, 'Akses ditolak.');
        }

        // 2. Validasi Waktu (SERVER-SIDE)
        $endTime = $hasilUjian->started_at->addMinutes($hasilUjian->ujian->durasi_menit);
        // Beri toleransi 5 detik untuk network delay
        if (now()->greaterThan($endTime->addSeconds(5))) {
            return $this->forceSubmit($hasilUjian, 'Waktu Anda habis. Jawaban terakhir tidak tersimpan.');
        }
        
        // 3. Validasi Input (Sama seperti sebelumnya)
        $request->validate([
            'jawaban' => 'required|array',
            'jawaban.*' => 'required|integer', // Memastikan semua soal dijawab
        ]);

        // 4. Hitung Skor (Sama seperti sebelumnya)
        $ujian = $hasilUjian->ujian->load('soals.pilihanJawabans'); // Eager load
        $total_soal = $ujian->soals->count();
        $jawaban_benar = 0;

        foreach ($ujian->soals as $soal) {
            $id_pilihan_user = $request->jawaban[$soal->id] ?? null;

            if ($id_pilihan_user) {
                // Gunakan relasi yang sudah di-load, bukan query baru
                $pilihan_benar = $soal->pilihanJawabans->firstWhere('apakah_benar', true);
                if ($pilihan_benar && $pilihan_benar->id == $id_pilihan_user) {
                    $jawaban_benar++;
                }
            }
        }

        $skor = ($total_soal > 0) ? ($jawaban_benar / $total_soal) * 100 : 0;

        // 5. Simpan Hasil (UPDATE, bukan CREATE)
        $hasilUjian->update([
            'skor' => $skor,
            'finished_at' => now(),
        ]);

        // Redirect ke halaman hasil dengan membawa ID ujian
        return redirect()->route('pengerjaan.result', $ujian->id)->with('status', 'Ujian telah selesai dikerjakan!');
    }

    /**
     * PERUBAHAN: Method result sekarang mengambil skor dari DB.
     * Tidak lagi bergantung pada session.
     */
    public function result(Ujian $ujian)
    {
        $hasil = HasilUjian::where('user_id', Auth::id())
                        ->where('ujian_id', $ujian->id)
                        ->whereNotNull('finished_at') // Pastikan yg sudah selesai
                        ->latest('finished_at') // Ambil yg terbaru
                        ->firstOrFail();

        $skor = $hasil->skor;
        return view('pengerjaan.result', compact('ujian', 'skor'));
    }

    /**
     * Helper function untuk submit paksa jika waktu habis.
     */
    private function forceSubmit(HasilUjian $hasilUjian, $message = 'Waktu habis! Ujian diselesaikan secara otomatis.')
    {
         if (!$hasilUjian->finished_at) {
            // Update skor (jika belum ada, akan jadi 0 karena tidak ada jawaban yg diproses)
            $hasilUjian->update([
                'skor' => $hasilUjian->skor ?? 0, // Set skor 0 jika masih null
                'finished_at' => now(),
            ]);
         }
        return redirect()->route('pengerjaan.result', $hasilUjian->ujian_id)->with('status', $message);
    }
}