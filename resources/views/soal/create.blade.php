{{-- PERUBAHAN 1: Menggunakan layout 'app-new' yang berbasis Bootstrap --}}
@extends('layouts.app-new')
@section('title', 'Tambah Soal untuk ' . $ujian->judul)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <h1 class="h3 mb-4">Formulir Soal Baru</h1>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                {{-- PERUBAHAN 2: Tambahkan enctype untuk file upload --}}
                <form action="{{ route('soal.store', $ujian) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Input Teks Pertanyaan --}}
                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label fw-bold">Teks Pertanyaan</label>
                        <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan') }}</textarea>
                        @error('pertanyaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- PERUBAHAN 3: Field baru untuk unggah gambar soal --}}
                    <div class="mb-4">
                        <label for="gambar_soal" class="form-label">Unggah Gambar (Opsional)</label>
                        <input class="form-control @error('gambar_soal') is-invalid @enderror" type="file" id="gambar_soal" name="gambar_soal">
                        @error('gambar_soal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <h5 class="mb-3">Pilihan Jawaban</h5>
                    <p class="text-muted small">Pilih salah satu radio button sebagai penanda jawaban yang benar.</p>
                    
                    {{-- Menggunakan 'pilihan[0]', 'pilihan[1]', dst. agar konsisten dengan controller --}}
                    @for ($i = 0; $i < 4; $i++)
                    <div class="input-group mb-3">
                        <div class="input-group-text">
                            {{-- Gunakan $i sebagai value radio button --}}
                            <input class="form-check-input mt-0" type="radio" value="{{ $i }}" name="jawaban_benar" required {{ old('jawaban_benar') == $i ? 'checked' : '' }}>
                        </div>
                        <input type="text" class="form-control @error('pilihan.'.$i) is-invalid @enderror" name="pilihan[{{ $i }}]" placeholder="Teks Pilihan {{ $i + 1 }}" required value="{{ old('pilihan.'.$i) }}">
                    </div>
                    @endfor
                    @error('pilihan.*') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                    @error('jawaban_benar') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('ujian.show', $ujian) }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold">Simpan Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection