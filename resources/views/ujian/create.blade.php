@extends('layouts.app-new')
@section('title', 'Buat Ujian Baru')

@section('content')
<h1 class="h3 mb-4">Formulir Ujian Baru</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('ujian.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="judul" class="form-label">Judul Ujian</label>
                <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" required>
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="durasi_menit" class="form-label">Durasi (dalam menit)</label>
                <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit') }}" required min="1">
                @error('durasi_menit') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('ujian.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan & Tambah Soal</button>
            </div>
        </form>
    </div>
</div>
@endsection