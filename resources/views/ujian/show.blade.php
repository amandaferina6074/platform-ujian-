@extends('layouts.app-new')
@section('title', 'Detail: ' . $ujian->judul)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $ujian->judul }}</h1>
        <p class="text-muted">{{ $ujian->deskripsi }}</p>
    </div>
    <form action="{{ route('ujian.destroy', $ujian) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ujian ini beserta semua soalnya?');">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus Ujian</button>
    </form>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Soal</h5>
        <a href="{{ route('soal.create', $ujian) }}" class="btn btn-success fw-bold">
            <i class="bi bi-plus-lg"></i> Tambah Soal
        </a>
    </div>
    <div class="card-body">
        @forelse ($ujian->soals as $key => $soal)
            <div class="mb-4 pb-3 border-bottom">
                <p class="fw-bold"> {{ $key + 1 }}. {{ $soal->pertanyaan }}</p>

                {{-- PERUBAHAN: Tampilkan gambar jika ada --}}
                @if ($soal->image_path)
                <div class="mb-3">
                    {{-- Storage::url() akan mengonversi path 'public/...' menjadi URL yg bisa diakses --}}
                    <img src="{{ Storage::url($soal->image_path) }}" alt="Gambar Soal" class="img-fluid rounded" style="max-height: 300px;">
                </div>
                @endif

                <ul class="list-unstyled ps-4">
                    @foreach ($soal->pilihanJawabans as $pilihan)
                        <li class="{{ $pilihan->apakah_benar ? 'text-success fw-bold' : '' }}">
                            <i class="bi {{ $pilihan->apakah_benar ? 'bi-check-circle-fill' : 'bi-circle' }} me-2"></i>
                            {{ $pilihan->teks_pilihan }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="alert alert-info text-center">Belum ada soal untuk ujian ini.</div>
        @endforelse
    </div>
</div>
@endsection