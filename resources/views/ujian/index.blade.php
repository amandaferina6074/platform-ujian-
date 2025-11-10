@extends('layouts.app-new') {{-- Menggunakan layout baru --}}
@section('title', 'Daftar Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Daftar Ujian</h1>
    {{-- Tombol ini hanya muncul jika yang login adalah Dosen --}}
    @if(auth()->user()->role == 'dosen')
    <a href="{{ route('ujian.create') }}" class="btn btn-primary fw-bold">
        <i class="bi bi-plus-lg"></i> Buat Ujian Baru
    </a>
    @endif
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @forelse ($ujians as $ujian)
            <div class="d-flex justify-content-between align-items-center p-3 mb-2 border-bottom">
                <div>
                    <h5 class="mb-1">{{ $ujian->judul }}</h5>
                    <p class="text-muted mb-0">{{ $ujian->deskripsi ?: 'Tidak ada deskripsi' }} | {{ $ujian->soals_count }} Soal</p>
                </div>
                <div>
                    {{-- Tombol untuk Dosen --}}
                    @if(auth()->user()->role == 'dosen')
                    <a href="{{ route('ujian.show', $ujian) }}" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-eye"></i> Detail & Kelola Soal
                    </a>
                    @endif

                    {{-- Tombol untuk Mahasiswa --}}
                    @if(auth()->user()->role == 'mahasiswa')
                    <a href="{{ route('pengerjaan.start', $ujian) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-pencil-square"></i> Kerjakan
                    </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center">Belum ada ujian yang tersedia.</div>
        @endforelse
    </div>
    @if($ujians->hasPages())
    <div class="card-footer bg-light">
        {{ $ujians->links() }}
    </div>
    @endif
</div>
@endsection