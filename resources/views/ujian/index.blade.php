@extends('layouts.app-new')
@section('title', 'Daftar Ujian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Daftar Ujian</h1>
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
                    <p class="text-muted mb-0">
                        {{ $ujian->deskripsi ?: 'Tidak ada deskripsi' }} | {{ $ujian->soals_count }} Soal
                        
                        {{-- 
                        ======================================================
                         PERBAIKAN: Tambahkan @if untuk mengecek data NULL
                        ======================================================
                        --}}
                        <br>
                        <small class="text-info fw-bold">
                            @if($ujian->available_from && $ujian->available_to)
                                Dibuka: {{ $ujian->available_from->format('d M Y, H:i') }} | 
                                Ditutup: {{ $ujian->available_to->format('d M Y, H:i') }}
                            @else
                                (Jadwal belum diatur)
                            @endif
                        </small>
                    </p>
                </div>
                <div>
                    {{-- Tombol untuk Dosen --}}
                    @if(auth()->user()->role == 'dosen')
                    <a href="{{ route('ujian.show', $ujian) }}" class="btn btn-info btn-sm text-white">
                        <i class="bi bi-eye"></i> Detail & Kelola Soal
                    </a>
                    @endif

                    {{-- Logika Tombol Mahasiswa --}}
                    @if(auth()->user()->role == 'mahasiswa')
                        @php
                            $now = now();
                        @endphp

                        {{-- PERBAIKAN: Cek juga jika datanya NULL --}}
                        @if(!$ujian->available_from || !$ujian->available_to)
                            <span class="btn btn-secondary btn-sm disabled">
                                <i class="bi bi-gear"></i> Menunggu Jadwal
                            </span>
                        @elseif ($now->lt($ujian->available_from))
                            <span class="btn btn-secondary btn-sm disabled">
                                <i class="bi bi-calendar-x"></i> Belum Dibuka
                            </span>
                        @elseif ($now->gt($ujian->available_to))
                            <span class="btn btn-danger btn-sm disabled">
                                <i class="bi bi-calendar-check"></i> Sudah Ditutup
                            </span>
                        @else
                            <a href="{{ route('pengerjaan.start', $ujian) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-pencil-square"></i> Kerjakan
                            </a>
                        @endif
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