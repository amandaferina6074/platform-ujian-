@extends('layouts.app-new')
@section('title', 'Buat Ujian Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <h1 class="h3 mb-4">Formulir Ujian Baru</h1>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('ujian.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="judul" class="form-label fw-bold">Judul Ujian</label>
                        <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul" value="{{ old('judul') }}" required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="durasi_menit" class="form-label fw-bold">Durasi Pengerjaan (Menit)</label>
                                <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit') }}" required min="1">
                                @error('durasi_menit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="available_from" class="form-label fw-bold">Ujian Dibuka Mulai</label>
                                <input type="datetime-local" class="form-control @error('available_from') is-invalid @enderror" id="available_from" name="available_from" value="{{ old('available_from') }}" required>
                                @error('available_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="available_to" class="form-label fw-bold">Ujian Ditutup Pada</label>
                                <input type="datetime-local" class="form-control @error('available_to') is-invalid @enderror" id="available_to" name="available_to" value="{{ old('available_to') }}" required>
                                @error('available_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    @error('available_to') <div class="text-danger small mb-3">{{ $message }}</div> @enderror


                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('ujian.index') }}" class="btn btn-secondary me-2">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold">Simpan & Lanjut Tambah Soal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection