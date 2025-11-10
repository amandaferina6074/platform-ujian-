@extends('layouts.app-new')
@section('title', 'Hasil Ujian')

@section('content')
<div class="card shadow-sm text-center">
    <div class="card-body p-5">
        <h1 class="h4">Hasil Ujian Selesai</h1>
        <p class="text-muted">Anda telah menyelesaikan ujian: <strong>{{ $ujian->judul }}</strong></p>
        <hr>
        <h2 class="display-4 fw-bold text-primary">{{ number_format($skor, 0) }}</h2>
        <p class="lead">Skor Anda</p>
        <a href="{{ route('ujian.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Ujian</a>
    </div>
</div>
@endsection