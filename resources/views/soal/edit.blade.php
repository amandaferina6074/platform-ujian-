@extends('layouts.app-new')
@section('title', 'Edit Soal')

@section('content')
<div class="row justify-content-center" x-data="{ type: '{{ old('type', $soal->type) }}' }">
    <div class="col-md-10">
        <h1 class="h3 mb-4">Formulir Edit Soal</h1>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('soal.update', $soal) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') {{-- Method untuk Update --}}
                    
                    <div class="mb-3">
                        <label for="type" class="form-label fw-bold">Tipe Soal</label>
                        <select class="form-select" id="type" name="type" x-model="type">
                            <option value="pilihan_ganda" {{ $soal->type == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                            <option value="esai" {{ $soal->type == 'esai' ? 'selected' : '' }}>Esai</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pertanyaan" class="form-label fw-bold">Teks Pertanyaan</label>
                        <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan" rows="3" required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                        @error('pertanyaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="gambar_soal" class="form-label">Unggah Gambar Baru (Opsional)</label>
                        <input class="form-control @error('gambar_soal') is-invalid @enderror" type="file" id="gambar_soal" name="gambar_soal">
                        @error('gambar_soal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if ($soal->image_path)
                            <small class="text-muted mt-1 d-block">Gambar saat ini: <img src="{{ Storage::disk('public_uploads')->url($soal->image_path) }}" alt="Gambar Saat Ini" class="img-thumbnail" style="max-height: 100px;"></small>
                            <input type="checkbox" name="hapus_gambar" id="hapus_gambar" value="1">
                            <label for="hapus_gambar"><small>Centang untuk menghapus gambar saat ini</small></label>
                        @endif
                    </div>

                    <hr>

                    <div x-show="type === 'pilihan_ganda'">
                        <h5 class="mb-3">Pilihan Jawaban</h5>
                        <p class="text-muted small">Pilih salah satu radio button sebagai penanda jawaban yang benar.</p>
                        
                        @php
                            $jawaban_benar = $soal->pilihanJawabans->firstWhere('apakah_benar', true);
                            $kunci = $jawaban_benar ? $jawaban_benar->id : null;
                        @endphp

                        @for ($i = 0; $i < 4; $i++)
                        @php
                            $pilihan = $soal->pilihanJawabans[$i] ?? null;
                            $pilihan_id = $pilihan ? $pilihan->id : null;
                            
                            // Logika untuk menentukan $kunci_index (0, 1, 2, atau 3)
                            $kunci_index = null;
                            if ($jawaban_benar) {
                                foreach ($soal->pilihanJawabans as $index => $pil) {
                                    if ($pil->id == $jawaban_benar->id) {
                                        $kunci_index = $index;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" 
                                       type="radio" 
                                       value="{{ $i }}" 
                                       name="jawaban_benar" 
                                       {{ old('jawaban_benar', $kunci_index) == $i ? 'checked' : '' }}
                                       x-bind:required="type === 'pilihan_ganda'"
                                       x-bind:disabled="type !== 'pilihan_ganda'">
                            </div>
                            
                            <input type="text" 
                                   class="form-control @error('pilihan.'.$i) is-invalid @enderror" 
                                   name="pilihan[{{ $i }}]" 
                                   placeholder="Teks Pilihan {{ $i + 1 }}" 
                                   value="{{ old('pilihan.'.$i, $pilihan ? $pilihan->teks_pilihan : '') }}"
                                   x-bind:required="type === 'pilihan_ganda'"
                                   x-bind:disabled="type !== 'pilihan_ganda'">
                        </div>
                        @endfor
                        @error('pilihan.*') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                        @error('jawaban_benar') <div class="text-danger small mb-3">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('ujian.show', $soal->ujian_id) }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold">Update Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection