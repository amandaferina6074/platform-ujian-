@extends('layouts.app-new')
@section('title', 'Mulai: ' . $ujian->judul)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm text-center">
            <div class="card-header bg-white p-4">
                <h1 class="h3 mb-1">{{ $ujian->judul }}</h1>
                <p class="text-muted">{{ $ujian->deskripsi }}</p>
            </div>
            <div class="card-body p-5">
                <div class="row">
                    <div class="col-md-6">
                        <i class="bi bi-card-checklist display-4 text-primary"></i>
                        <h5 class="mt-2">Total Soal</h5>
                        <p class="fs-4 fw-bold">{{ $ujian->soals_count }} Soal</p>
                    </div>
                    <div class="col-md-6">
                        <i class="bi bi-alarm-fill display-4 text-danger"></i>
                        <h5 class="mt-2">Waktu Pengerjaan</h5>
                        <p class="fs-4 fw-bold">{{ $ujian->durasi_menit }} Menit</p>
                    </div>
                </div>
                <hr class="my-4">
                <p class="text-muted">Waktu akan dihitung mundur saat Anda menekan tombol "Mulai Ujian". Pastikan Anda siap.</p>
                
                <form action="{{ route('pengerjaan.begin', $ujian) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg fw-bold px-5">
                        <i class="bi bi-play-circle-fill"></i> Mulai Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection