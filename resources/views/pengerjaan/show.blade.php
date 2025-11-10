@extends('layouts.app-new')
@section('title', 'Mengerjakan: ' . $hasilUjian->ujian->judul)

@section('content')

{{-- 
================================================================
PERUBAHAN 1: TIMER DENGAN ALPINE.JS
================================================================
--}}
<div 
    x-data="timerData('{{ $endTime->toIso8601String() }}')" 
    x-init="initTimer()"
    class="card shadow-sm mb-4 sticky-top"
>
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $hasilUjian->ujian->judul }}</h5>
        <div class="bg-danger text-white px-3 py-2 rounded shadow-sm">
            <i class="bi bi-alarm-fill"></i>
            <span class="fw-bold fs-5" x-text="displayTime">00:00</span>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        {{-- 
        ================================================================
        PERUBAHAN 2: FORM SEKARANG MENGARAH KE RUTE BARU
        ================================================================
        --}}
        <form id="form-ujian" action="{{ route('pengerjaan.submit', $hasilUjian) }}" method="POST">
            @csrf
            
            {{-- Loop Soal (Sama seperti sebelumnya) --}}
            @foreach ($hasilUjian->ujian->soals as $key => $soal)
                <div class="mb-4 pb-3 border-bottom">
                    <p class="fw-bold">{{ $key + 1 }}. {{ $soal->pertanyaan }}</p>
                    
                    @if ($soal->image_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($soal->image_path) }}" alt="Gambar Soal" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                    @endif

                    <div class="ps-4">
                        @foreach ($soal->pilihanJawabans as $pilihan)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jawaban[{{ $soal->id }}]" id="pilihan-{{ $pilihan->id }}" value="{{ $pilihan->id }}" required>
                                <label class="form-check-label" for="pilihan-{{ $pilihan->id }}">
                                    {{ $pilihan->teks_pilihan }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan ujian ini?')">
                    <i class="bi bi-check-circle-fill"></i> Selesaikan Ujian
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 
================================================================
PERUBAHAN 3: SCRIPT UNTUK TIMER
================================================================
--}}
<script>
    function timerData(endTime) {
        return {
            endTime: new Date(endTime),
            displayTime: '00:00',
            interval: null,

            initTimer() {
                this.updateTime(); // Panggil sekali saat init
                this.interval = setInterval(() => {
                    this.updateTime();
                }, 1000);
            },

            updateTime() {
                const now = new Date();
                const remaining = this.endTime.getTime() - now.getTime();

                if (remaining <= 0) {
                    clearInterval(this.interval);
                    this.displayTime = '00:00';
                    this.submitForm();
                } else {
                    const minutes = Math.floor((remaining / 1000 / 60) % 60);
                    const seconds = Math.floor((remaining / 1000) % 60);
                    
                    this.displayTime = `${this.pad(minutes)}:${this.pad(seconds)}`;
                }
            },

            pad(num) {
                return num < 10 ? '0' + num : num;
            },

            submitForm() {
                alert('Waktu habis! Jawaban Anda akan dikirim secara otomatis.');
                document.getElementById('form-ujian').submit();
            }
        };
    }
</script>
@endsection