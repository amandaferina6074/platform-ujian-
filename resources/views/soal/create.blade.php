@extends('layouts.app-new')
@section('title', 'Tambah Soal untuk ' . $ujian->judul)

@section('content')
<div class="row justify-content-center" x-data="{ type: '{{ old('type', 'pilihan_ganda') }}' }">
    <div class="col-md-10">
        <h1 class="h3 mb-4">Formulir Soal Baru</h1>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('soal.store', $ujian) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="type" class="form-label fw-bold">Tipe Soal</label>
                        <select class="form-select" id="type" name="type" x-model="type">
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="esai">Esai</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label fw-bold">Teks Pertanyaan</label>
                        <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan') }}</textarea>
                        @error('pertanyaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="gambar_soal" class="form-label">Unggah Gambar (Opsional)</label>
                        <input class="form-control @error('gambar_soal') is-invalid @enderror" type="file" id="gambar_soal" name="gambar_soal">
                        @error('gambar_soal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>

                    <div x-show="type === 'pilihan_ganda'">
                        <h5 class="mb-3">Pilihan Jawaban</h5>
                        <p class="text-muted small">Pilih salah satu radio button sebagai penanda jawaban yang benar.</p>
                        
                        @for ($i = 0; $i < 4; $i++)
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                {{-- 
                                ======================================================
                                PERBAIKAN: Tambahkan x-bind:disabled
                                ======================================================
                                --}}
                                <input class="form-check-input mt-0" 
                                       type="radio" 
                                       value="{{ $i }}" 
                                       name="jawaban_benar" 
                                       {{ old('jawaban_benar') == $i ? 'checked' : '' }}
                                       x-bind:required="type === 'pilihan_ganda'"
                                       x-bind:disabled="type !== 'pilihan_ganda'"> {{-- <-- TAMBAHKAN INI --}}
                            </div>
                            
                            {{-- 
                            ======================================================
                            PERBAIKAN: Tambahkan x-bind:disabled
                            ======================================================
                            --}}
                            <input type="text" 
                                   class="form-control @error('pilihan.'.$i) is-invalid @enderror" 
                                   name="pilihan[{{ $i }}]" 
                                   placeholder="Teks Pilihan {{ $i + 1 }}" 
                                   value="{{ old('pilihan.'.$i) }}"
                                   x-bind:required="type === 'pilihan_ganda'"
                                   x-bind:disabled="type !== 'pilihan_ganda'"> {{-- <-- TAMBAHKAN INI --}}
                        </div>
                        @endfor
                        @error('pilihan.*') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                        @error('jawaban_benar') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                    </div>

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